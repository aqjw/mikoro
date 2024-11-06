import { Events, Plugin } from 'xgplayer';
import ButtonHandler from './ButtonHandler.ts';
import DropdownHandler from './DropdownHandler.ts';
import InteractionFocusManager from './InteractionFocusManager.ts';
import SpinnerOverlay from './SpinnerOverlay.ts';
import './index.scss';

export default class PlaylistPlugin extends Plugin {
  static get pluginName() {
    return 'PlaylistPlugin';
  }

  static get defaultConfig() {
    return {
      position: Plugin.POSITIONS.ROOT_TOP,
      index: 0,
      playbackManager: null,
      isSingleEpisode: false,
    };
  }

  afterCreate() {
    this._loading = true;
    this.playbackManager = this.config.playbackManager;
    this.focusManager = new InteractionFocusManager(this);
    this.spinnerOverlay = new SpinnerOverlay(this, 'xg-spinner');

    this.initDropdowns();
    this.initButtons();
    this.loadEpisodes();
    this.listenEvents();
  }

  initDropdowns() {
    const translationsDropdown = new DropdownHandler(this, {
      container: 'xg-translations',
      class: 'w-long',
      invisible: false,
      formatOptionLabel: (option, forLabel = true) => {
        if (!forLabel && !this.config.isSingleEpisode) {
          return `<div class="with-counter">
                <div class="with-counter__label">${option.label}</div>
                <div class="with-counter__count">${option.episodes_count}</div>
            </div>`;
        }

        return option.label;
      },
    });

    const episodesDropdown = new DropdownHandler(this, {
      container: 'xg-episodes',
      class: 'w-short',
      invisible: this.config.isSingleEpisode,
      filter: (options) => {
        return options.filter(
          (option) => option.translation_id === this.dropdowns.translations.getSelected()
        );
      },
      formatOptionLabel: (option) => `${option.label} серия`,
    });

    translationsDropdown.emitter
      .on('selected-updated', () => {
        this.dropdowns.episodes.refreshOptions();
        if (!this.dropdowns.episodes.hasSelected()) {
          return;
        }

        const episodes = this.dropdowns.episodes.getOptions();
        const currentEpisode = this.dropdowns.episodes.getSelectedOption();
        let newEpisode = null;

        if (currentEpisode) {
          newEpisode = episodes.find(
            (item) =>
              currentEpisode.label === item.label &&
              item.translation_id === this.dropdowns.translations.getSelected()
          );
        }

        if (!newEpisode) {
          newEpisode = this.dropdowns.episodes.last();
        }

        this.dropdowns.episodes.setSelected(newEpisode?.id);
      })
      .on('open-toggle', (isOpen) => {
        episodesDropdown.suspendEmitter().closeDropdown();
        this.player.innerStates.isActiveLocked = isOpen;
        if (!isOpen) {
          this.player.focus();
        }
      });

    episodesDropdown.emitter
      .on('selected-updated', () => {
        this.updatePlaybackState();
        this.player.onWaiting();

        this.buttons.prev.disabled(!this.dropdowns.episodes.prev());
        this.buttons.next.disabled(!this.dropdowns.episodes.next());

        const resetTime = !translationsDropdown.wasRecentlyChanged();
        this.spinnerOverlay.show();

        this.playbackManager
          .reloadLinks()
          .then(() => {
            if (resetTime) {
              this.player.currentTime = 0;
            }
            this.player.definitionList = this.playbackManager.links.value;
          })
          .catch((error) => {
            // TODO: handle error
            console.error(error);
          })
          .finally(() => {
            this.spinnerOverlay.hide();
          });
      })
      .on('open-toggle', (isOpen) => {
        translationsDropdown.suspendEmitter().closeDropdown();
        this.player.innerStates.isActiveLocked = isOpen;
        if (!isOpen) {
          this.player.focus();
        }
      });

    this.dropdowns = {
      translations: translationsDropdown,
      episodes: episodesDropdown,
    };
  }

  initButtons() {
    const prevButton = new ButtonHandler(this, {
      container: 'xg-prev',
      label: 'Previous',
    });

    const nextButton = new ButtonHandler(this, {
      container: 'xg-next',
      label: 'Next',
    });

    prevButton.emitter.on('click', () => {
      const prevEpisode = this.dropdowns.episodes.prev();
      if (prevEpisode) {
        this.dropdowns.episodes.setSelected(prevEpisode.id);
      }
    });

    nextButton.emitter.on('click', () => {
      const nextEpisode = this.dropdowns.episodes.next();
      if (nextEpisode) {
        this.dropdowns.episodes.setSelected(nextEpisode.id);
      }
    });

    this.buttons = {
      prev: prevButton,
      next: nextButton,
    };
  }

  listenEvents() {
    this.onPause = () => {
      this.playbackManager.savePlaybackState();
    };

    this.onTimeUpdate = () => {
      this.updatePlaybackState();
    };

    this.onDefinitionChanged = (e) => {
      this.player.play();
    };

    this.on(Events.PAUSE, this.onPause);
    this.on(Events.TIME_UPDATE, this.onTimeUpdate);
    this.on(Events.AFTER_DEFINITION_CHANGE, this.onDefinitionChanged);

    this.player.getPlugin('overlay').addCustomHook(() => {
      try {
        return !(
          this.dropdowns.translations.getDropdownStatus() ||
          this.dropdowns.episodes.getDropdownStatus()
        );
      } finally {
        this.dropdowns.translations.closeDropdown();
        this.dropdowns.episodes.closeDropdown();
      }
    });
  }

  destroy() {
    this.off(Events.PAUSE, this.onPause);
    this.off(Events.TIME_UPDATE, this.onTimeUpdate);
    this.off(Events.AFTER_DEFINITION_CHANGE, this.onDefinitionChanged);
    //
    this.playbackManager.savePlaybackState();
    this.dropdowns.translations.destroy();
    this.dropdowns.episodes.destroy();
    this.buttons.prev.destroy();
    this.buttons.next.destroy();
    this.focusManager.destroy();
  }

  loadEpisodes() {
    this.spinnerOverlay.show();
    this.playbackManager
      .loadEpisodes()
      .then(() => {
        const { translations, episodes } = this.playbackManager.episodes.value;

        this.dropdowns.translations.setOptions(translations);
        this.dropdowns.episodes.setOptions(episodes);

        this.applyPlaybackState(this.playbackManager.playbackState.value);
      })
      .catch((error) => {
        // TODO: handle error
        console.error(error);
      })
      .finally(() => {
        this._loading = false;
        this.spinnerOverlay.hide();
      });
  }

  applyPlaybackState(playbackState) {
    let translationId = playbackState?.translation_id;
    let episodeId = playbackState?.episode_id;

    if (!translationId) {
      translationId = this.dropdowns.translations.first()?.id;
    }

    if (!episodeId) {
      episodeId = this.dropdowns.episodes.first()?.id;
    }

    this.dropdowns.translations.setSelected(translationId);
    this.dropdowns.episodes.setSelected(episodeId);

    this.player.currentTime = playbackState?.time ?? 0;
  }

  updatePlaybackState() {
    this.playbackManager.setPlaybackState({
      episode_id: this.dropdowns.episodes.getSelected(),
      translation_id: this.dropdowns.translations.getSelected(),
      time: Math.ceil(this.player.currentTime),
    });
  }

  render() {
    return `
    <xg-playlist class="w-full text-white xgplayer-ignore xgplayer-playlist">
        <xg-translations></xg-translations>
        -
        <xg-prev></xg-prev>
        <xg-episodes></xg-episodes>
        <xg-next></xg-next>
        -
        <xg-spinner></xg-spinner>
    </xg-playlist>`;
  }
}

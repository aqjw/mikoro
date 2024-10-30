import { Events, Plugin } from 'xgplayer';
import DropdownHandler from './DropdownHandler';
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

    this.initDropdowns();
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
          return `<div class="with-episodes-count">
                <div class="with-episodes-count__label">${option.label}</div>
                <div class="with-episodes-count__count">${option.episodes_count}</div>
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

    // TODO: resetTime - not working
    let resetTime = false;

    translationsDropdown.emitter
      .on('selected-updated', () => {
        this.dropdowns.episodes.applyFilter();
        if (!this.dropdowns.episodes.hasSelected()) {
          return;
        }

        const episodes = this.dropdowns.episodes.getOptions();
        const currentEpisode = this.dropdowns.episodes.getSelectedOption();
        let newEpisode = null;

        if (currentEpisode) {
          resetTime = false;
          newEpisode = episodes.find(
            (item) =>
              currentEpisode.label === item.label &&
              item.translation_id === this.dropdowns.translations.getSelected()
          );
        }

        if (!newEpisode) {
          resetTime = true;
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

    this.useOverlayClickHook = () => {
      try {
        return !(
          this.dropdowns.translations.isDropdownOpen() ||
          this.dropdowns.episodes.isDropdownOpen()
        );
      } finally {
        this.dropdowns.translations.closeDropdown();
        this.dropdowns.episodes.closeDropdown();
      }
    };

    this.on(Events.PAUSE, this.onPause);
    this.on(Events.TIME_UPDATE, this.onTimeUpdate);
    this.on(Events.AFTER_DEFINITION_CHANGE, this.onDefinitionChanged);

    this.player.getPlugin('overlay').customHookCb = this.useOverlayClickHook;
  }

  destroy() {
    this.off(Events.PAUSE, this.onPause);
    this.off(Events.TIME_UPDATE, this.onTimeUpdate);
    this.off(Events.AFTER_DEFINITION_CHANGE, this.onDefinitionChanged);
    //
    this.playbackManager.savePlaybackState();
    this.dropdowns.translations.destroy();
    this.dropdowns.episodes.destroy();
  }

  loadEpisodes() {
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
      <div class="w-full text-white xgplayer-ignore xgplayer-playlist">
        <xg-translations></xg-translations>
        <xg-episodes></xg-episodes>
      </div>`;
  }
}

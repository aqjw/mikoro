import { Events, Plugin, Util } from 'xgplayer';

export default class PlaylistPlugin extends Plugin {
  static get pluginName() {
    return 'PlaylistPlugin';
  }

  static get defaultConfig() {
    return {
      position: Plugin.POSITIONS.ROOT_TOP,
      index: 0,
      playbackManager: null,
    };
  }

  afterCreate() {
    this._loading = true;

    this._playbackManager = this.config.playbackManager;
    console.log('playbackManager', this._playbackManager);
    // TODO: if no playbackManager - do something

    this._d = {
      isSingleEpisode: true,
      translations: [],
      episodes: [],
      selected: {
        translations: null,
        episodes: null,
      },
    };

    this.loadEpisodes();
    this.listenEvents();
  }

  listenEvents() {
    this.onSelectContainerClick = (e) => {
      const node = e.delegateTarget;
      this.toggleDropdownVisibility(node.id);
    };

    this.onDropdownOptionClick = (e) => {
      e.preventDefault();
      e.stopPropagation();
      const node = e.delegateTarget;
      this.changeSelectedOption(node.dataset.sid, +node.dataset.oid);
    };

    this.onPause = () => {
      this._playbackManager.savePlaybackState();
    };

    this.onTimeUpdate = () => {
      this.setPlaybackState();
    };

    this.onDefinitionChanged = (e) => {
      this.player.play();
    };

    this.useOverlayClickHook = () => {
      try {
        return !this.isDropdownOpen();
      } finally {
        this.closeDropdowns();
      }
    };

    this.bind('.select-container', 'click', this.onSelectContainerClick);
    this.bind('.dropdown-option', 'click', this.onDropdownOptionClick);
    //
    this.on(Events.PAUSE, this.onPause);
    this.on(Events.TIME_UPDATE, this.onTimeUpdate);
    this.on(Events.AFTER_DEFINITION_CHANGE, this.onDefinitionChanged);
    //
    this.player.getPlugin('overlay').customHookCb = this.useOverlayClickHook;
  }

  destroy() {
    this.unbind('.select-container', 'click', this.onSelectContainerClick);
    this.unbind('.dropdown-option', 'click', this.onDropdownOptionClick);
    //
    this.off(Events.PAUSE, this.onPause);
    this.off(Events.TIME_UPDATE, this.onTimeUpdate);
    this.off(Events.AFTER_DEFINITION_CHANGE, this.onDefinitionChanged);
    //
    this._playbackManager.savePlaybackState();
  }

  loadEpisodes() {
    this._playbackManager
      .loadEpisodes()
      .then(() => {
        const {
          single_episode: isSingleEpisode,
          translations,
          episodes,
        } = this._playbackManager.episodes.value;

        this._d.isSingleEpisode = isSingleEpisode;
        this._d.translations = translations;
        this._d.episodes = episodes;

        this.populateDropdownOptions('translations', this._d.translations);
        this.populateDropdownOptions('episodes', this.filteredEpisodeOptions());

        if (this._d.isSingleEpisode) {
          this.removeDropdown('episodes');
        }

        this.applyPlaybackState(this._playbackManager.playbackState.value);
      })
      .catch(() => {
        // TODO: handle error
      })
      .finally(() => {
        this._loading = false;
      });
  }

  applyPlaybackState(playbackState) {
    const translationId = playbackState?.translation_id ?? this._d.translations[0]?.id;
    const episodeId =
      playbackState?.episode_id ?? this.filteredEpisodeOptions(translationId)[0]?.id;
    const playbackTime = playbackState?.time ?? 0;

    this.changeSelectedOption('translations', translationId);
    this.changeSelectedOption('episodes', episodeId);
    this.player.currentTime = playbackTime;
  }

  setPlaybackState() {
    const translationId = this._d.selected.translations;

    if (this._d.isSingleEpisode) {
      const episodeId = this.filteredEpisodeOptions(translationId)[0]?.id;
      this._d.selected.episodes = episodeId ?? this._d.selected.episodes;
    }

    this._playbackManager.setPlaybackState({
      episode_id: this._d.selected.episodes,
      translation_id: translationId,
      time: Math.ceil(this.player.currentTime),
    });
  }

  getOptionLabel(selectId, option, withEpisodes = false) {
    if (!option) return '';

    if (selectId === 'episodes') {
      return `${option.label} серия`;
    }

    if (withEpisodes && !this._d.isSingleEpisode) {
      return `<div class="with-episodes-count">
                <div class="with-episodes-count__label">${option.label}</div>
                <div class="with-episodes-count__count">${option.episodes_count}</div>
            </div>`;
    }

    return option.label;
  }

  isDropdownOpen() {
    return Boolean(this.find('.select-container.open'));
  }

  closeDropdowns() {
    this.find('.select-container.open')?.classList.remove('open');
    this.player.innerStates.isActiveLocked = false;
    this.player.focus();
  }

  toggleDropdownVisibility(selectId) {
    if (this.find(`.select-container.open:not(#${selectId})`)) {
      this.closeDropdowns();
    }

    const opened = this.find(`#${selectId}`).classList.toggle('open');
    this.player.innerStates.isActiveLocked = opened;

    if (opened) {
      const optionId = this._d.selected[selectId];
      if (optionId) {
        const element = this.find(`#${selectId} .dropdown li[data-oid="${optionId}"]`);
        if (element) {
          const dropdown = this.find(`#${selectId} .dropdown`);
          dropdown.scrollTop = element.offsetTop - dropdown.offsetTop;
        }
      }
    } else {
      this.player.focus();
    }
  }

  changeSelectedOption(selectId, optionId, resetTime = true) {
    if (this._d.selected[selectId] === optionId) {
      return;
    }

    this._d.selected[selectId] = +optionId;
    this.highlightSelectedOption(selectId, optionId);
    this.updateDisplayedOptionLabel(selectId, optionId);
    this.closeDropdowns();

    if (selectId === 'translations') {
      this.populateDropdownOptions('episodes', this.filteredEpisodeOptions());
      this.switchEpisodeTranslation();
    }

    if (!this._loading && selectId === 'episodes') {
      this.setPlaybackState();

      this._playbackManager
        .reloadLinks()
        .then(() => {
          if (resetTime) {
            this.player.currentTime = 0;
          }

          this.player.definitionList = this._playbackManager.links.value;
        })
        .catch((error) => {
          // TODO: handle error
          console.error(error);
        });
    }
  }

  switchEpisodeTranslation() {
    const episodes = this._d.episodes;
    const selectedTranslationId = this._d.selected.translations;
    const selectedEpisodeId = this._d.selected.episodes;

    if (!selectedEpisodeId) return;

    let resetTime = false;

    const currentEpisode = episodes.find((item) => item.id === selectedEpisodeId);
    let newEpisode = episodes.find(
      (item) =>
        currentEpisode.label === item.label &&
        item.translation_id === selectedTranslationId
    );

    if (!newEpisode) {
      resetTime = true;
      newEpisode = episodes
        .filter((item) => item.translation_id === selectedTranslationId)
        .last();
    }

    this.changeSelectedOption('episodes', newEpisode.id, resetTime);
  }

  highlightSelectedOption(selectId, optionId) {
    this.find(`#${selectId} .dropdown-option.selected`)?.classList.remove('selected');
    this.find(`#${selectId} .dropdown-option[data-oid="${optionId}"]`)?.classList.add(
      'selected'
    );
  }

  updateDisplayedOptionLabel(selectId, optionId) {
    const option = this._d[selectId].find((item) => item.id == optionId);
    const label = this.getOptionLabel(selectId, option);
    this.find(`#${selectId} .selected-option`).innerHTML = label;
  }

  filteredEpisodeOptions(translationId = this._d.selected.translations) {
    return this._d.episodes.filter((item) => item.translation_id == translationId);
  }

  isSelected(selectId, optionId) {
    return this._d.selected[selectId] === optionId;
  }

  populateDropdownOptions(selectId, options) {
    const html = options
      .map((option) => {
        const selectedClass = this.isSelected(selectId, option.id) ? 'selected' : '';
        return Util.createDom('li', this.getOptionLabel(selectId, option, true), {
          class: `dropdown-option ${selectedClass}`,
          'data-sid': selectId,
          'data-oid': option.id,
        }).outerHTML;
      })
      .join('');

    this.find(`#${selectId} .dropdown`).innerHTML = html;
  }

  removeDropdown(selectId) {
    this.find(`#${selectId}`).remove();
  }

  selectNode(selectId, width = 'short') {
    return `
        <div id="${selectId}" class="select-container w-${width}">
            <div class="selected-option-container">
                <div class="selected-option"></div>
                <svg class="icon" viewBox="0 0 330 330">
                    <path d="m250.606 154.389-150-149.996c-5.857-5.858-15.355-5.858-21.213.001-5.857 5.858-5.857 15.355.001 21.213l139.393 139.39L79.393 304.394c-5.857 5.858-5.857 15.355.001 21.213C82.322 328.536 86.161 330 90 330s7.678-1.464 10.607-4.394l149.999-150.004a14.996 14.996 0 0 0 0-21.213"/>
                </svg>
            </div>

            <div class="dropdown-container">
                <ul class="dropdown"></ul>
            </div>
        </div>`;
  }

  render() {
    return `
    <div class="w-full text-white xgplayer-ignore xgplayer-playlist">
        ${this.selectNode('translations', 'long')}
        ${this.selectNode('episodes', 'short')}
    </div>`;
  }
}

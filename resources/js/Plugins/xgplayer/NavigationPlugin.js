import { Plugin, Events } from 'xgplayer';

export default class NavigationPlugin extends Plugin {
  static get pluginName() {
    return 'NavigationPlugin';
  }

  static get defaultConfig() {
    return {
      position: Plugin.POSITIONS.ROOT_TOP,
      index: 0,
      titleId: null,
    };
  }

  afterCreate() {
    this._d = {
      translations: [],
      episodes: [],
      selected: {
        translations: null,
        episodes: null,
      },
    };

    this.fetchTitleEpisodes(this.config.titleId);
    this.bindEvents();
  }

  bindEvents() {
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

    this.onPlayOrOverlayClick = () => {
      this.closeDropdowns();
    };

    this.bind('.select-container', 'click', this.onSelectContainerClick);
    this.bind('.dropdown-option', 'click', this.onDropdownOptionClick);
    this.on(Events.PLAY, this.onPlayOrOverlayClick);
    this.on('overlay_click', this.onPlayOrOverlayClick);
  }

  destroy() {
    this.unbind('.select-container', 'click', this.onSelectContainerClick);
    this.unbind('.dropdown-option', 'click', this.onDropdownOptionClick);
    this.off(Events.PLAY, this.onPlayOrOverlayClick);
    this.off('overlay_click', this.onPlayOrOverlayClick);
  }

  async fetchTitleEpisodes(titleId) {
    try {
      const { data } = await axios.get(route('upi.title.episodes', titleId));
      this._d.translations = data.translations;
      this._d.episodes = data.episodes;

      this.applyPlaybackState(data.playback_state);
      this.populateDropdownOptions('translations', this._d.translations);
      this.filterEpisodeOptions();
    } catch (error) {
      console.error(error);
    }
  }

  applyPlaybackState(playbackState) {
      const { translationId, episodeId, time } = playbackState;
      console.log('s', {translationId,episodeId});
    this.changeSelectedOption('translations', translationId);
    this.changeSelectedOption('episodes', episodeId);
    this.player.currentTime = time;
  }

  getOptionLabel(selectId, option) {
    if (selectId === 'episodes') {
      return `Серия ${option.label}`;
    }
    return option.label;
  }

  closeDropdowns() {
    this.find('.select-container.open')?.classList.remove('open');
  }

  toggleDropdownVisibility(selectId) {
    if (this.find(`.select-container.open:not(#${selectId})`)) {
      this.closeDropdowns();
    }

    const opened = this.find(`#${selectId}`).classList.toggle('open');

    if (opened) {
      this.player.pause();

      const optionId = this._d.selected[selectId];
      if (optionId) {
        const element = this.find(`#${selectId} .dropdown li[data-oid="${optionId}"]`);
        if (element) {
          const dropdown = this.find(`#${selectId} .dropdown`);
          dropdown.scrollTop = element.offsetTop - dropdown.offsetTop;
        }
      }
    }
  }

  changeSelectedOption(selectId, optionId) {
    if (this._d.selected[selectId] === optionId) {
      return;
    }

    this._d.selected[selectId] = +optionId;
    this.highlightSelectedOption(selectId, optionId);
    this.updateDisplayedOptionLabel(selectId, optionId);
    this.closeDropdowns();

    if (selectId === 'translations') {
      this.filterEpisodeOptions();
      this.switchEpisodeTranslation();
    }
  }

  switchEpisodeTranslation() {
    const episodes = this._d.episodes;
    const selectedTranslationId = this._d.selected.translations;
    const selectedEpisodeId = this._d.selected.episodes;

    if (!selectedEpisodeId) return;

    const currentEpisode = episodes.find((item) => item.id === selectedEpisodeId);

    let newEpisode =
      episodes.find(
        (item) =>
          currentEpisode.label === item.label &&
          item.translation_id === selectedTranslationId
      ) ||
      episodes.filter((item) => item.translation_id === selectedTranslationId).last();

    this.changeSelectedOption('episodes', newEpisode?.id);
  }

  highlightSelectedOption(selectId, optionId) {
    this.find(`#${selectId} .dropdown-option.selected`)?.classList.remove('selected');
    this.find(`#${selectId} .dropdown-option[data-oid="${optionId}"]`)?.classList.add(
      'selected'
    );
  }

  updateDisplayedOptionLabel(selectId, optionId) {
    const option = this._d[selectId].find((item) => item.id == optionId);
    this.find(`#${selectId} .selected-option`).innerHTML = this.getOptionLabel(
      selectId,
      option
    );
  }

  filterEpisodeOptions() {
    this.populateDropdownOptions(
      'episodes',
      this._d.episodes.filter(
        (item) => item.translation_id == this._d.selected.translations
      )
    );
  }

  populateDropdownOptions(selectId, options) {
    const html = options
      .map(
        (option) => `
        <li class="dropdown-option" data-sid="${selectId}" data-oid="${option.id}">
          ${this.getOptionLabel(selectId, option)}
        </li>`
      )
      .join('');

    this.find(`#${selectId} .dropdown`).innerHTML = html;
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
    <div class="w-full text-white xgplayer-ignore xgplayer-navigation">
        ${this.selectNode('translations', 'long')}
        ${this.selectNode('episodes', 'short')}
    </div>`;
  }
}

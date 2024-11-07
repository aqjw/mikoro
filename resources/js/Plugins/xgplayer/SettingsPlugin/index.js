import { Plugin } from 'xgplayer';
import backSvg from './assets/back.svg';
import checkSvg from './assets/check.svg';
import definitionSvg from './assets/definition.svg';
import definitionsSvg from './assets/definitions';
import playbackRateSvg from './assets/playbackRate.svg';
import playbackSpinnerSvg from './assets/playbackSpinner.svg';
import settingsSvg from './assets/settings.svg';
import './index.scss';

export default class SettingsPlugin extends Plugin {
  static get pluginName() {
    return 'settings';
  }

  static get defaultConfig() {
    const getDefinitionIcon = (definition) => {
      const resolutionIcons = {
        360: 'sd',
        480: 'hq',
        720: 'hd',
        1080: 'fhd',
        // TODO: 2k - https://uxwing.com/8k-label-color-icon/
        //   1440: 'qhd',
        2160: 'uhd',
        4320: 'q8k',
      };

      const closestResolution = Object.keys(resolutionIcons)
        .reverse()
        .find((res) => definition >= res);

      return definitionsSvg[resolutionIcons[closestResolution] || 'fhd'];
    };

    return {
      position: Plugin.POSITIONS.CONTROLS_RIGHT,
      index: 1,
      tabs: [
        {
          name: 'definition',
          width: '12rem',
          getTitle: () => 'Definition',
          getLabel: (player) => {
            return `
                <div class="flex justify-between items-center img-wave-on-hover">
                    <div class="flex gap-2 items-center">
                        <img src="${definitionSvg}" />
                        <div>Definition</div>
                    </div>
                    <div class="text-xs">
                        ${player.curDefinition.text}p
                    </div>
                </div>
            `;
          },
          getItems: (player) => {
            return [...player.definitionList].map((item) => ({
              ...item,
              icon: getDefinitionIcon(+item.definition),
              iconClass:
                'item-icon-wave !h-[initial] !w-8 border-px !border-white/50 rounded-md',
              containerClass: '',
              active: item.definition === player.curDefinition.definition,
            }));
          },
          onClick: (e, player, definition) => {
            player.changeDefinition(definition);
          },
        },
        {
          name: 'playbackRate',
          width: '12em',
          getTitle: () => 'Playback Speed',
          getLabel: (player) => {
            const playbackRate = player.getPlugin('playbackRate');
            const current = playbackRate.config.list.find(
              ({ rate }) => rate === playbackRate.curValue
            ).text;
            return `
                <div class="flex justify-between items-center img-wave-on-hover">
                    <div class="flex gap-2 items-center">
                        <img src="${playbackRateSvg}" />
                        <div>Playback Speed</div>
                    </div>
                    <div class="text-xs">${current}</div>
                </div>`;
          },
          getItems: (player) => {
            const playbackRate = player.getPlugin('playbackRate');
            return [...playbackRate.config.list].map((item) => ({
              ...item,
              icon: playbackSpinnerSvg,
              iconClass: '!h-5 icon-item',
              containerClass: `rate-item rate-item-${item.rate}`.replace('.', '_'),
              active: item.rate === player.playbackRate,
            }));
          },
          onClick: (e, player, { rate }) => {
            const playbackRate = player.getPlugin('playbackRate');
            if (!rate || rate === playbackRate.curValue) {
              return false;
            }
            const props = {
              playbackRate: {
                from: player.playbackRate,
                to: rate,
              },
            };
            playbackRate.emitUserAction(e, 'change_rate', { props });
            playbackRate.curValue = rate;
            player.playbackRate = rate;
          },
        },
      ],
    };
  }

  registerIcons() {
    return {
      settings: { icon: settingsSvg, class: '' },
    };
  }

  afterCreate() {
    this._d = {
      isActive: false,
    };

    this.initIcons();

    this.bind('.xgplayer-icon', ['touchend', 'click'], this.toggleModalHandler);
    this.bind(
      '.xgplayer-modal-tab[data-tab="list"] ul > li',
      ['touchend', 'click'],
      this.toggleTabListHandler
    );

    this.bind(
      '.xgplayer-modal-tab:not([data-tab="list"]) ul > li',
      ['touchend', 'click'],
      this.toggleTabHandler
    );

    this.bind('.xgplayer-modal-tab .list-header', ['touchend', 'click'], this.backTabHandler);

    this.player.getPlugin('overlay').addCustomHook(() => {
      try {
        return !this._d.isActive;
      } finally {
        this._d.isActive = false;
        this.player.innerStates.isActiveLocked = false;
        this.player.focus();
        this.root.classList.remove('xgplayer-active');
      }
    });
  }

  initIcons() {
    const { icons } = this;
    this.appendChild('.xgplayer-icon', icons.settings);
  }

  toggleModalHandler = () => {
    this._d.isActive = !this._d.isActive;
    this.player.innerStates.isActiveLocked = this._d.isActive;

    if (this._d.isActive) {
      this.renderTabs();
    } else {
      this.player.focus();
    }

    this.find(`.xgplayer-modal`).style.width = null;
    this.root.classList.toggle('xgplayer-active', this._d.isActive);
  };

  toggleTabListHandler = (e) => {
    const tabName = e.delegateTarget.dataset.target;
    this.switchTab(tabName);
    this.renderTabItems(tabName);
  };

  toggleTabHandler = (e) => {
    const tabName = e.delegateTarget.dataset.tab;
    const index = e.delegateTarget.dataset.index;
    const tab = this.config.tabs.find((tab) => tab.name === tabName);
    const items = tab.getItems(this.player);
    tab.onClick(e, this.player, items[index]);
    this.toggleModalHandler();
  };

  backTabHandler = (e) => {
    this.switchTab('list');
  };

  switchTab(tabName) {
    this.find(`.xgplayer-modal-tab[data-tab].show-tab`).classList.remove('show-tab');
    this.find(`.xgplayer-modal-tab[data-tab="${tabName}"]`).classList.add('show-tab');

    const tab = this.config.tabs.find((tab) => tab.name === tabName);
    this.find(`.xgplayer-modal`).style.width = tab?.width || null;
  }

  destroy() {
    this.unbind('.xgplayer-icon', ['touchend', 'click'], this.toggleModalHandler);
    this.unbind(
      '.xgplayer-modal-tab .list-header',
      ['touchend', 'click'],
      this.backTabHandler
    );
    this.unbind(
      '.xgplayer-modal-tab[data-tab="list"] ul > li',
      ['touchend', 'click'],
      this.toggleTabHandler
    );
  }

  renderTabs() {
    this.find('.xgplayer-modal').innerHTML = [
      { name: 'list', show: true },
      ...this.config.tabs,
    ]
      .map((item) => {
        let headerHtml = '';
        if (item.name !== 'list') {
          headerHtml = `
            <div class="list-header">
                <img src="${backSvg}" class="!h-4" />
                <span>${item.getTitle()}</span>
            </div>`;
        }

        return `
        <div
            class="xgplayer-modal-tab ${item.show ? 'show-tab' : ''}"
            data-tab="${item.name}"
        >
            ${headerHtml}
            <ul></ul>
        </div>`;
      })
      .join('');

    this.find(`.xgplayer-modal-tab[data-tab="list"] ul`).innerHTML = this.config.tabs
      .map((item) => `<li data-target="${item.name}">${item.getLabel(this.player)}</li>`)
      .join('');
  }

  renderTabItems(tab) {
    const items = this.config.tabs.find(({ name }) => name === tab).getItems(this.player);
    this.find(`.xgplayer-modal-tab[data-tab="${tab}"] ul`).innerHTML = items
      .map(
        (item, index) =>
          `<li
                class="flex justify-between items-center ${item.active ? 'item-active' : ''} ${
            item.containerClass
          }"
                data-index="${index}"
                data-tab="${tab}"
            >
            <div class="flex gap-2 items-center">
                <div class="w-4">
                    <img src="${checkSvg}"
                        class="${item.active ? '' : '!hidden'} !h-4" />
                </div>
                <div>${item.text}</div>
            </div>

            <div class="${item.icon ? '' : '!hidden'} ${item.iconClass}">
                <img src="${item.icon}" class="!w-[initial] !h-full" />
            </div>
          </li>`
      )
      .join('');
  }

  render() {
    return `
      <xg-icon class="xgplayer-settings xgplayer-ignore">
        <div class="xgplayer-icon"></div>
        <xg-modal class="xgplayer-modal"></xg-modal>
      </xg-icon>`;
  }
}

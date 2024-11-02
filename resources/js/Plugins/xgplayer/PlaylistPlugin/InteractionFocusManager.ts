import { Plugin, Sniffer, Events } from 'xgplayer';

export default class InteractionFocusManager {
  private plugin: Plugin;

  constructor(plugin: Plugin) {
    this.plugin = plugin;
    this.initEventListeners();
  }

  initEventListeners(): void {
    this.onMouseEnter = (e) => {
      const { player, playerConfig } = this.plugin;
      playerConfig.closeControlsBlur && player.focus({ autoHide: false });
    };

    this.onMouseLeave = (e) => {
      this.plugin.player.focus();
    };

    const { isMobileSimulateMode } = this.plugin.playerConfig;
    if (Sniffer.device !== 'mobile' && isMobileSimulateMode !== 'mobile') {
      this.plugin.bind('mouseenter', this.onMouseEnter);
      this.plugin.bind('mouseleave', this.onMouseLeave);
    }
  }

  destroy(): void {
    if (Sniffer.device !== 'mobile') {
      this.plugin.unbind('mouseenter', this.onMouseEnter);
      this.plugin.unbind('mouseleave', this.onMouseLeave);
    }
  }
}

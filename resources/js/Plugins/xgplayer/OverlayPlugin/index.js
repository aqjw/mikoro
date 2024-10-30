import { Plugin } from 'xgplayer';
import './index.scss';

export default class OverlayPlugin extends Plugin {
  static get pluginName() {
    return 'overlay';
  }

  static get defaultConfig() {
    return {
      position: Plugin.POSITIONS.ROOT,
      index: 0,
    };
  }

  runCustomHook(cb) {
    return () => this.customHookCb() && cb();
  }

  afterCreate() {
    let isDblclick = false;
    let clickTimer = null;

    this.onDblclick = () => {
      isDblclick = true;
      clearTimeout(clickTimer);
      this.player.getPlugin('fullscreen').handleFullscreen();
    };

    this.customHookCb = () => true;
    this.onClick = this.runCustomHook(() => {
      clickTimer = setTimeout(() => {
        if (isDblclick) {
          isDblclick = false;
          return;
        }

        this.player.paused ? this.player.play() : this.player.pause();
      }, 300);
    });

    this.bind('.xgplayer-overlay', 'dblclick', this.onDblclick);
    this.bind('.xgplayer-overlay', 'click', this.onClick);
  }

  destroy() {
    this.unbind('.xgplayer-overlay', 'dblclick', this.onDblclick);
    this.unbind('.xgplayer-overlay', 'click', this.onClick);
  }

  render() {
    return '<div class="xgplayer-overlay"></div>';
  }
}

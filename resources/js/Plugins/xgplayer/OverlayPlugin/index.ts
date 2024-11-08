import { Plugin } from 'xgplayer';
import './index.scss';

export default class OverlayPlugin extends Plugin {
  private customHookCbs: Array<() => boolean> = [];

  static get pluginName() {
    return 'overlay';
  }

  static get defaultConfig() {
    return {
      position: Plugin.POSITIONS.ROOT,
      index: 0,
    };
  }

  addCustomHook(cb: () => boolean) {
    this.customHookCbs.push(cb);
  }

  runCustomHooks(cb: () => void) {
    return () => {
      for (const hook of this.customHookCbs) {
        if (!hook()) return;
      }
      cb();
    };
  }

  afterCreate() {
    this.customHookCbs = [];

    let isDblclick = false;
    let clickTimer: NodeJS.Timeout | null = null;

    this.onDblclick = () => {
      isDblclick = true;
      clearTimeout(clickTimer as NodeJS.Timeout);
      this.player.getPlugin('fullscreen').handleFullscreen();
    };

    this.onClick = this.runCustomHooks(() => {
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
    this.customHookCbs = [];
  }

  hide() {
    this.root.classList.add('hidden');
  }

  show() {
    this.root.classList.remove('hidden');
  }

  render() {
    return '<div class="xgplayer-overlay"></div>';
  }
}

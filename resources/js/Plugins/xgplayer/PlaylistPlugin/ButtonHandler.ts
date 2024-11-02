import { ref, Ref, watch } from 'vue';
import { Plugin, Util } from 'xgplayer';
import Emitter from '../Emitter';

interface ButtonConfig {
  container: string;
  label: string;
  class?: string;
  recentClickedThreshold?: number;
}

export default class ButtonHandler {
  private emitter: Emitter;
  private plugin: Plugin;
  private config: ButtonConfig;

  public isDisabled: Ref<boolean>;
  private lastClicked: Ref<number>;

  constructor(plugin: Plugin, config: ButtonConfig) {
    this.emitter = new Emitter();
    this.plugin = plugin;
    this.config = this.initializeConfig(config);

    this.isDisabled = ref(this.config.disabled);
    this.lastClicked = ref(0);

    this.renderButton();
    this.initWatchers();
    this.initEventListeners();
  }

  private initializeConfig(config: ButtonConfig): ButtonConfig {
    return {
      class: '',
      disabled: false,
      recentClickedThreshold: 500,
      ...config,
    };
  }

  wasRecentlyClicked(): boolean {
    return Date.now() - this.lastClicked.value <= this.config.recentClickedThreshold;
  }

  disabled(state = true): void {
    this.isDisabled.value = state;
  }

  selector(selector = ''): string {
    return `${this.config.container} ${selector}`.trim();
  }

  el(selector = ''): HTMLElement | null {
    return this.plugin.find(this.selector(selector));
  }

  initWatchers(): void {
    watch(this.isDisabled, (val) => {
      this.emitter.emit('disabled-toggle', val);
      this.toggleDisabledClass(val);
    });
  }

  initEventListeners(): void {
    this.handleClick = (e: Event) => {
      if (this.isDisabled.value) return;
      e.preventDefault();
      e.stopPropagation();
      this.lastClicked.value = Date.now();
      this.emitter.emit('click');
    };

    this.plugin.bind(this.selector(), 'click', this.handleClick);
  }

  toggleDisabledClass(isDisabled: boolean): void {
    const buttonNode = this.el('.playlist-button');
    if (!buttonNode) return;

    if (isDisabled) {
      buttonNode.classList.add('disabled');
    } else {
      buttonNode.classList.remove('disabled');
    }
  }

  destroy(): void {
    this.emitter.offAll();
    this.el()?.remove();
    this.plugin.unbind(this.selector(), 'click', this.handleClick);
  }

  renderButton(): void {
    const html = `<div class="playlist-button ${this.config.class}">${this.config.label}</div>`;
    const node = Util.createDom('button', html, {});
    this.plugin.appendChild(this.config.container, node);

    if (this.isDisabled.value) {
      node.classList.add('disabled');
    }
  }
}

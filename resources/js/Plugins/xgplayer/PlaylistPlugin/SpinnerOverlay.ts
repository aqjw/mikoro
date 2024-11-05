import { Plugin } from 'xgplayer';

export default class SpinnerOverlay {
  private plugin: Plugin;
  private container: string | HTMLElement;
  private indicatorElement: HTMLElement;

  constructor(plugin: Plugin, container: string | HTMLElement) {
    this.plugin = plugin;
    this.container = container;
    this.indicatorElement = this.createIndicatorElement();
    this.appendToContainer();
    this.hide();
  }

  private createIndicatorElement(): HTMLElement {
    const indicator = document.createElement('div');
    indicator.className = 'spinner-overlay';
    indicator.innerHTML = '<div class="spinner"></div>';
    return indicator;
  }

  private appendToContainer(): void {
    this.plugin.appendChild(this.container, this.indicatorElement);
  }

  show(): void {
    this.indicatorElement.style.display = 'block';
  }

  hide(): void {
    this.indicatorElement.style.display = 'none';
  }

  destroy(): void {
    this.indicatorElement.remove();
  }
}

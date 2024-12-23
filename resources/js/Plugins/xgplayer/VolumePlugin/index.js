import { Events, Plugin, Sniffer, Util } from 'xgplayer';
import volumeLargeSvg from './assets/volumeLarge.svg';
import volumeMutedSvg from './assets/volumeMuted.svg';
import volumeSmallSvg from './assets/volumeSmall.svg';
import './index.scss';

export default class VolumePlugin extends Plugin {
  static get pluginName() {
    return 'volume';
  }

  /**
   * @type IVolumeConfig
   */
  static get defaultConfig() {
    return {
      position: Plugin.POSITIONS.CONTROLS_LEFT,
      index: 1,
      disable: false,
      showValueLabel: false,
      default: 1,
      miniVolume: 0.2,
    };
  }

  registerIcons() {
    return {
      volumeSmall: { icon: volumeSmallSvg, class: 'xg-volume-small' },
      volumeLarge: { icon: volumeLargeSvg, class: 'xg-volume' },
      volumeMuted: { icon: volumeMutedSvg, class: 'xg-volume-mute' },
    };
  }

  afterCreate() {
    this._timerId = null;
    this._d = {
      isStart: false,
      isMoving: false,
      isActive: false,
    };
    if (this.config.disable) {
      return;
    }

    this.initIcons();

    const { commonStyle, volume } = this.playerConfig;
    if (commonStyle.volumeColor) {
      this.find('.xgplayer-drag').style.backgroundColor = commonStyle.volumeColor;
    }
    this.changeMutedHandler = this.hook(
      'mutedChange',
      (e) => {
        this.changeMuted(e);
      },
      {
        pre: (e) => {
          e.preventDefault();
          e.stopPropagation();
        },
      }
    );

    this._onMouseenterHandler = this.hook('mouseenter', this.onMouseenter);
    this._onMouseleaveHandler = this.hook('mouseleave', this.onMouseleave);

    if (
      !(Sniffer.device === 'mobile') &&
      this.playerConfig.isMobileSimulateMode !== 'mobile'
    ) {
      this.bind('mouseenter', this._onMouseenterHandler);

      const controlsPlugin = this.player.getPlugin('controls');
      if (controlsPlugin) {
        controlsPlugin.bind('blur', this._onMouseleaveHandler);
        controlsPlugin.bind('mouseleave', this._onMouseleaveHandler);
      }

      this.bind('.xgplayer-slider', 'mousedown', this.onBarMousedown);
      this.bind('.xgplayer-slider', 'mousemove', this.onBarMouseMove);
      this.bind('.xgplayer-slider', 'mouseup', this.onBarMouseUp);
    }

    this.bind('.xgplayer-icon', ['touchend', 'click'], this.changeMutedHandler);

    this.on(Events.VOLUME_CHANGE, this.onVolumeChange);
    this.once(Events.LOADED_DATA, this.onVolumeChange);

    if (Util.typeOf(volume) !== 'Number') {
      this.player.volume = this.config.default;
    }

    this.onVolumeChange();
  }

  onBarMousedown = (e) => {
    const { player } = this;
    const bar = this.find('.xgplayer-bar');
    Util.event(e);

    const barRect = bar.getBoundingClientRect();
    const pos = Util.getEventPos(e, player.zoom);
    const width = pos.clientX - barRect.left; // Изменяем высоту на ширину
    pos.w = width;
    pos.barW = barRect.width;
    this.pos = pos;
    if (width < -2) {
      return;
    }
    this.updateVolumePos(width, e);
    document.addEventListener('mouseup', this.onBarMouseUp);
    this._d.isStart = true;
    return false;
  };

  onBarMouseMove = (e) => {
    const { _d } = this;
    if (!_d.isStart) return;
    const { pos, player } = this;
    e.preventDefault();
    e.stopPropagation();
    Util.event(e);
    const _ePos = Util.getEventPos(e, player.zoom);
    _d.isMoving = true;
    const w = _ePos.clientX - pos.clientX + pos.w;
    if (w > pos.barW) return;
    this.updateVolumePos(w, e);
  };

  onBarMouseUp = (e) => {
    Util.event(e);
    document.removeEventListener('mouseup', this.onBarMouseUp);
    const { _d } = this;
    _d.isStart = false;
    _d.isMoving = false;
  };

  updateVolumePos(width, event) {
    const { player } = this;
    const drag = this.find('.xgplayer-drag');
    const bar = this.find('.xgplayer-bar');
    if (!bar || !drag) return;

    const now = parseInt((width / bar.getBoundingClientRect().width) * 1000, 10);
    drag.style.width = `${width}px`;
    const to = Math.max(Math.min(now / 1000, 1), 0);
    const props = {
      volume: {
        from: player.volume,
        to,
      },
    };
    if (player.muted) {
      props.muted = {
        from: true,
        to: false,
      };
    }
    this.emitUserAction(event, 'change_volume', {
      muted: player.muted,
      volume: player.volume,
      props,
    });
    player.volume = to;
    player.muted && (player.muted = false);

    if (this.config.showValueLabel) {
      this.updateVolumeValue();
    }
  }

  /**
   * 修改音量数值标签
   *
   * @memberof Volume
   */
  updateVolumeValue() {
    const { volume, muted } = this.player;
    const $labelValue = this.find('.xgplayer-value-label');
    const vol = Math.max(Math.min(volume, 1), 0);

    // Math.ceil有精度问题，比如Math.ceil(0.55 * 100) == 56，因此这里使用Math.round
    // 0.58 * 100 === 57.99999999999999
    // 0.55 * 100 === 55.00000000000001
    $labelValue.innerText = muted ? 0 : Math.round(vol * 100);
  }

  /**
   * @desc 聚焦
   */
  focus() {
    const { player } = this;
    player.focus({ autoHide: false });
    if (this._timerId) {
      Util.clearTimeout(this, this._timerId);
      this._timerId = null;
    }
    Util.addClass(this.root, 'slide-show');
  }

  /**
   * 失去焦点
   * @param { number } delay 延迟隐藏时长，ms
   * @param { boolean } isForce 是否立即隐藏控制栏
   * @param { Event} [e] 事件
   * @returns
   */
  unFocus(delay = 100, isForce = true, e) {
    const { _d, player } = this;
    if (_d.isActive) {
      return;
    }
    if (this._timerId) {
      Util.clearTimeout(this, this._timerId);
      this._timerId = null;
    }
    this._timerId = Util.setTimeout(
      this,
      () => {
        if (!_d.isActive) {
          isForce ? player.blur() : player.focus();
          Util.removeClass(this.root, 'slide-show');
          _d.isStart && this.onBarMouseUp(e);
        }
        this._timerId = null;
      },
      delay
    );
  }

  onMouseenter = (e) => {
    this._d.isActive = true;
    this.focus();
    this.emit('icon_mouseenter', {
      pluginName: this.pluginName,
    });
  };

  onMouseleave = (e) => {
    if (this._d.isMoving) return;
    this._d.isActive = false;
    this.unFocus(100, false, e);
    this.emit('icon_mouseleave', {
      pluginName: this.pluginName,
    });
  };

  changeMuted(e) {
    // e.preventDefault()
    e && e.stopPropagation();
    const { player, _d } = this;
    // 取消bar的状态
    _d.isStart && this.onBarMouseUp(e);
    this.emitUserAction(e, 'change_muted', {
      muted: player.muted,
      volume: player.volume,
      props: {
        muted: {
          from: player.muted,
          to: !player.muted,
        },
      },
    });
    if (player.volume > 0) {
      player.muted = !player.muted;
    }
    if (player.volume < 0.01) {
      player.volume = this.config.miniVolume;
    }
  }

  onVolumeChange = (e) => {
    if (!this.player) {
      return;
    }
    const { muted, volume } = this.player;
    if (!this._d.isMoving) {
      const width = muted || volume === 0 ? '4px' : `${volume * 100}%`;
      this.find('.xgplayer-drag').style.width = width;
      if (this.config.showValueLabel) {
        this.updateVolumeValue();
      }
    }
    this.animate(muted, volume);
  };

  animate(muted, volume) {
    if (muted || volume === 0) {
      this.setAttr('data-state', 'mute');
    } else if (volume < 0.5 && this.icons.volumeSmall) {
      this.setAttr('data-state', 'small');
    } else {
      this.setAttr('data-state', 'normal');
    }
  }

  initIcons() {
    const { icons } = this;
    this.appendChild('.xgplayer-icon', icons.volumeSmall);
    this.appendChild('.xgplayer-icon', icons.volumeLarge);
    this.appendChild('.xgplayer-icon', icons.volumeMuted);
  }

  destroy() {
    if (this._timerId) {
      Util.clearTimeout(this, this._timerId);
      this._timerId = null;
    }
    this.unbind('mouseenter', this.onMouseenter);

    // Используем плагин controls для отвязки событий blur и mouseleave
    const controlsPlugin = this.player.getPlugin('controls');
    if (controlsPlugin) {
      controlsPlugin.unbind('blur', this.onMouseleave);
      controlsPlugin.unbind('mouseleave', this.onMouseleave);
    }

    this.unbind('.xgplayer-slider', 'mousedown', this.onBarMousedown);
    this.unbind('.xgplayer-slider', 'mousemove', this.onBarMouseMove);
    this.unbind('.xgplayer-slider', 'mouseup', this.onBarMouseUp);
    document.removeEventListener('mouseup', this.onBarMouseUp);

    this.unbind(
      '.xgplayer-icon',
      Sniffer.device === 'mobile' ? 'touchend' : 'click',
      this.changeMutedHandler
    );
  }

  render() {
    if (this.config.disable) {
      return;
    }
    const volume = this.config.default || this.player.volume;
    const isShowVolumeValue = this.config.showValueLabel;
    return `
      <xg-icon class="xgplayer-volume" data-state="normal">
        <div class="xgplayer-icon"></div>
        <xg-slider class="xgplayer-slider">
          ${isShowVolumeValue ? `<div class="xgplayer-value-label">${volume * 100}</div>` : ''}
          <div class="xgplayer-bar-wrap">
            <div class="xgplayer-bar">
                <xg-drag class="xgplayer-drag" style="width: ${volume * 100}%">
                    <div class="xgplayer-drag-btn"></div>
                </xg-drag>
            </div>
          </div>
        </xg-slider>
      </xg-icon>`;
  }
}

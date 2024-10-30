export default class Emitter {
  constructor() {
    this.events = {};
    this.isActive = true;
  }

  on(name, cb) {
    if (!this.events[name]) {
      this.events[name] = [];
    }
    this.events[name].push(cb);

    return this;
  }

  off(name, cb) {
    if (this.events[name]) {
      this.events[name] = this.events[name].filter((callback) => callback !== cb);
    }

    return this;
  }

  emit(name, ...args) {
    if (this.isActive && this.events[name]) {
      this.events[name].forEach((callback) => callback(...args));
    }
  }

  start() {
    this.isActive = true;
  }

  stop() {
    this.isActive = false;
  }
}

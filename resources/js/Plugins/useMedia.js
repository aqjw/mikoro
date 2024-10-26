const useMedia = {
  image(image) {
    if (!image) return null;
    if (typeof image === 'string') {
      return image;
    }
    return {
      src: useMedia.original(image),
      lazySrc: useMedia.placeholder(image),
    };
  },
  /**
   * Generates the placeholder URL for an image
   * @param {Object} image - The image object
   * @returns {string} - The placeholder URL or an empty string
   */
  placeholder(image) {
    if (typeof image === 'string') {
      return image;
    }
    return image && image.placeholder
      ? `${image.storage_url}conversions/${image.placeholder}`
      : '';
  },

  /**
   * Generates the original URL for an image
   * @param {Object} image - The image object
   * @returns {string} - The original URL or an empty string
   */
  original(image) {
    if (typeof image === 'string') {
      return image;
    }
    return image && image.original ? image.storage_url + image.original : '';
  },

  /**
   * Generates the srcset attribute value for responsive images
   * @param {Object} image - The image object
   * @returns {string} - The srcset attribute value or an empty string
   */
  srcset(image) {
    if (!image || !image.responsive) {
      return '';
    }

    const folder = 'responsive-images/';

    return Object.entries(image.responsive)
      .reverse()
      .map(([width, fileName]) => `${image.storage_url}${folder}${fileName} ${width}`)
      .join(', ');
  },
};

export default () => useMedia;

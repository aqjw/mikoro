export const stopEventPropagation = (event) => {
  event.stopPropagation();
  event.preventDefault();
};

export const getDefinitionIcon = (definition, definitionsSvg) => {
  const resolutionIcons = {
    360: 'sd',
    480: 'hq',
    720: 'hd',
    1080: 'fhd',
    // 2160: 'uhd', // TODO: no svg icon for this
    4320: 'q8k',
  };

  const closestResolution = Object.keys(resolutionIcons)
    .reverse()
    .find((res) => definition >= res);

  return definitionsSvg[resolutionIcons[closestResolution] || 'fhd'];
};

export const createListItemHtml = (item, index, tab) => {
  return `
        <li
          class="flex justify-between items-center
                ${item.active ? 'item-active' : ''}
                ${item.containerClass}"
          data-index="${index}"
          data-tab="${tab}"
        >
          <div class="flex gap-2 items-center">
            <div class="w-4">
              <img src="${item.active ? checkSvg : ''}"
                class="${item.active ? '' : '!hidden'} !h-4" />
            </div>
            <div>${item.text}</div>
          </div>
          <div class="${item.icon ? '' : '!hidden'} ${item.iconClass}">
            <img src="${item.icon}" class="!w-[initial] !h-full" />
          </div>
        </li>`;
};

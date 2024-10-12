export const initials = (string) => {
  return string
    .match(/(\b\S)?/g)
    .join('')
    .match(/(^\S|\S$)?/g)
    .join('');
};

// Function to replace BBCode with HTML
export const formatBbcodeToHtml = (text) => {
  return text
    .replace(/\[bold\](.*?)\[\/bold\]/g, '<strong>$1</strong>')
    .replace(/\[italic\](.*?)\[\/italic\]/g, '<em>$1</em>')
    .replace(/\[underline\](.*?)\[\/underline\]/g, '<u>$1</u>')
    .replace(/\[strike\](.*?)\[\/strike\]/g, '<s>$1</s>')
    .replace(/\[spoiler\](.*?)\[\/spoiler\]/g, '<spoiler>$1</spoiler>')
    .replace(/\[br\]/g, '<br>');
};

// Function to replace HTML with BBCode
export const formatHtmlToBbcode = (htmlContent) => {
  return htmlContent
    .replace(/<strong>(.*?)<\/strong>/g, '[bold]$1[/bold]')
    .replace(/<em>(.*?)<\/em>/g, '[italic]$1[/italic]')
    .replace(/<u>(.*?)<\/u>/g, '[underline]$1[/underline]')
    .replace(/<s>(.*?)<\/s>/g, '[strike]$1[/strike]')
    .replace(/<spoiler>(.*?)<\/spoiler>/g, '[spoiler]$1[/spoiler]')
    .replace(/<br\s*\/?>/g, '[br]')
    .replace(/<\/p><p>/g, '[br]')
    .replace(/<\/?p>/g, '');
};

export const scrollToElement = (element, duration) => {
  if (!element) {
    return;
  }

  const start = window.scrollY;
  const target =
    element.getBoundingClientRect().top +
    window.scrollY -
    window.innerHeight / 2 +
    element.offsetHeight / 2;
  const distance = target - start;
  const startTime = performance.now();

  function scrollAnimation(currentTime) {
    const elapsedTime = currentTime - startTime;
    const progress = Math.min(elapsedTime / duration, 1);
    window.scrollTo(0, start + distance * progress);

    if (elapsedTime < duration) {
      requestAnimationFrame(scrollAnimation);
    }
  }

  requestAnimationFrame(scrollAnimation);
};

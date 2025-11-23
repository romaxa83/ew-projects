/** @param {string} link */
export function download(link) {
	const a = document.createElement('a');
	a.href = link;
	a.download = link.split('/').pop();
	a.target = '_blank';
	a.click();
}

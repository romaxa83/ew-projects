/**
 * @param {number} percentage - float value from 0 to 1
 * @returns {string} CSS color (RGB)
 */
export function getRankColor(percentage) {
	const top = { r: 249, g: 105, b: 106 };
	const middle = { r: 238, g: 230, b: 131 };
	const bottom = { r: 99, g: 190, b: 123 };

	let startColor, endColor, ratio;

	if (percentage >= 0.5) {
		startColor = middle;
		endColor = top;
		ratio = (percentage - 0.5) * 2;
	} else {
		startColor = bottom;
		endColor = middle;
		ratio = percentage * 2;
	}

	const r = Math.round(startColor.r + (endColor.r - startColor.r) * ratio);
	const g = Math.round(startColor.g + (endColor.g - startColor.g) * ratio);
	const b = Math.round(startColor.b + (endColor.b - startColor.b) * ratio);

	return `rgb(${r}, ${g}, ${b})`;
}

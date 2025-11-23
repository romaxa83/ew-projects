import type { Dayjs } from 'dayjs';

declare global {
	type DateValue = Date | Dayjs | string | number;
}

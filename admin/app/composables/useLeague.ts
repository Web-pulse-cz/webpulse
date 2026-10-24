export interface LeagueActivityForm {
  _key: string;
  id?: number | null;
  name: string;
  description: string;
  value: number;
  max_per_day: number | null;
  counts_to_league: boolean;
  entries_count?: number | null;
}

export interface LeagueSectionForm {
  _key: string;
  id?: number | null;
  name: string;
  activities: LeagueActivityForm[];
}

/**
 * Shared League helpers: score formatting by scoring type and day labels.
 * scoring_type 'minutes' stores values in seconds, 'points' stores plain points.
 */
export function useLeague() {
  const scoringOptions = [
    { value: 'points', name: 'Body' },
    { value: 'minutes', name: 'Minuty' },
  ];

  const visibilityOptions = [
    { value: 'public', name: 'Pro všechny' },
    { value: 'invite', name: 'Jen na pozvání' },
  ];

  function scoringLabel(type: string): string {
    return type === 'minutes' ? 'Minuty' : 'Body';
  }

  /** Unit shown next to an activity value input. */
  function valueUnit(type: string): string {
    return type === 'minutes' ? 's' : 'b.';
  }

  /** Total formatted for display — minutes as "1 h 05 min" / "12:30 min", points as "120 b." */
  function formatScore(value: number | null | undefined, type: string): string {
    const num = Number(value) || 0;
    if (type !== 'minutes') return `${num.toLocaleString('cs-CZ')} b.`;

    const totalSeconds = Math.round(num);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    if (hours > 0) return `${hours} h ${String(minutes).padStart(2, '0')} min`;
    if (seconds > 0) return `${minutes}:${String(seconds).padStart(2, '0')} min`;
    return `${minutes} min`;
  }

  function dayLabel(date: string): {
    weekday: string;
    day: string;
    month: string;
    isToday: boolean;
  } {
    const d = new Date(date + 'T00:00:00');
    const today = new Date();
    const isToday =
      d.getFullYear() === today.getFullYear() &&
      d.getMonth() === today.getMonth() &&
      d.getDate() === today.getDate();

    return {
      weekday: d.toLocaleDateString('cs-CZ', { weekday: 'short' }),
      day: String(d.getDate()),
      month: d.toLocaleDateString('cs-CZ', { month: 'short' }),
      isToday,
    };
  }

  return { scoringOptions, visibilityOptions, scoringLabel, valueUnit, formatScore, dayLabel };
}

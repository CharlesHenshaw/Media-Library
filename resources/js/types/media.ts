export type MediaType = 'film' | 'tv' | 'game' | 'book';
export type EntryStatus = 'finished' | 'dnf';

export interface Media {
    type: MediaType;
    external_id: string;
    title: string;
    year: number | null;
    cover_url: string | null;
    creator: string | null;
}

export interface LibraryEntry {
    id: number;
    status: EntryStatus;
    rating: number | null;
    finished_on: string | null;
    media: Media;
}

export const mediaTypeLabels: Record<MediaType, string> = {
    film: 'Films',
    tv: 'TV shows',
    game: 'Games',
    book: 'Books',
};

export const statusLabels: Record<EntryStatus, string> = {
    finished: 'Finished',
    dnf: 'DNF',
};

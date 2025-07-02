import {Word} from "@/components/word/Word";

export interface Lesson {
    id: number;
    name: string;
    words: Word[]
}
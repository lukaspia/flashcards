import {Word} from "@/types/word.types";

export interface Lesson {
    id: number;
    name: string;
    words: Word[]
}
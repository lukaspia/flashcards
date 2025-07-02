import { createContext } from 'react';
import {Word} from "@/types/word.types";

interface WordsContextType {
    words: Word[];
    updateWords: (words: Word[]) => void;
    wordsCategories: any[];
}

const WordsContext = createContext<WordsContextType>({
    words: [],
    updateWords: (words: Word[]) => {},
    wordsCategories: [],
});

export default WordsContext;
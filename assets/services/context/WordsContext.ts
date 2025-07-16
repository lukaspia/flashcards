import { createContext } from 'react';
import {Word} from "../../types/word.types";

interface WordsContextType {
    words: Word[];
    updateWords: (words: Word[]) => void;
    wordsCategories: any[];
    sourceLanguage: string;
    targetLanguage: string;
}

const WordsContext = createContext<WordsContextType>({
    words: [],
    updateWords: (words: Word[]) => {},
    wordsCategories: [],
    sourceLanguage: '',
    targetLanguage: '',
});

export default WordsContext;
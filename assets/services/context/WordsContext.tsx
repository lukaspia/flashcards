import { createContext } from 'react';
import {Word} from "@/components/word/Word";

interface WordsContextType {
    words: Word[];
    updateWords: (words: Word[]) => void;
}

const WordsContext = createContext<WordsContextType>({
    words: [],
    updateWords: (words: Word[]) => {},
});

export default WordsContext;
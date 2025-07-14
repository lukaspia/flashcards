import { useState, useEffect } from 'react';
import {Word} from '../../types/word.types';

interface UseLessonNavigationProps {
    words: Word[];
    translationFirst: boolean;
}

interface UseLessonNavigationResult {
    index: number;
    setIndex: React.Dispatch<React.SetStateAction<number>>;
    isTranslation: boolean;
    setIsTranslation: React.Dispatch<React.SetStateAction<boolean>>;
    displayWord: string | null;
    handleShowWord: (direction: 'prev' | 'next') => void;
    lessonReset: () => void;
}

export const useLessonNavigation = ({ words, translationFirst }: UseLessonNavigationProps): UseLessonNavigationResult => {
    const [index, setIndex] = useState(0);
    const [isTranslation, setIsTranslation] = useState(false);
    const [displayWord, setDisplayWord] = useState<string | null>(null);

    const lessonReset = () => {
        setIndex(0);
        if (translationFirst) {
            setIsTranslation(true);
            setDisplayWord(words[0]?.translation || null);
        } else {
            setIsTranslation(false);
            setDisplayWord(words[0]?.basicWord || null);
        }
    };

    useEffect(() => {
        lessonReset();
    }, [words, translationFirst]);

    const handleShowWord = (direction: string) => {
        let i = index;

        if (!words.length) return;

        if (direction == 'prev') {
            if (translationFirst) {
                if (isTranslation) {
                    if (index > 0) {
                        i = index - 1;
                    }
                    setIndex(i);
                }

                setIsTranslation(true);
                setDisplayWord(words[i].translation);
            } else {
                if (!isTranslation) {
                    if (index > 0) {
                        i = index - 1;
                    }
                    setIndex(i);
                }

                setIsTranslation(false);
                setDisplayWord(words[i].basicWord);
            }
        } else {
            if (isTranslation) {
                if (!translationFirst) {
                    if (words.length > index + 1) {
                        i = index + 1;
                    }
                }
                setIndex(i);
                setIsTranslation(false);
                setDisplayWord(words[i].basicWord);
            } else {
                if (translationFirst) {
                    if (words.length > index + 1) {
                        i = index + 1;
                    }
                }
                setIndex(i);
                setIsTranslation(true);
                setDisplayWord(words[i].translation);
            }
        }
    }

    return {
        index,
        setIndex,
        isTranslation,
        setIsTranslation,
        displayWord,
        handleShowWord,
        lessonReset,
    };
};
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

    /*const handleShowWord = (direction: 'prev' | 'next') => {
        let newIndex = index;
        let newIsTranslation = isTranslation;

        if (!words.length) return;

        if (direction === 'prev') {
            if (translationFirst) {
                if (isTranslation) {
                    if (index > 0) newIndex = index - 1;
                    newIsTranslation = true;
                } else {
                    newIsTranslation = false; // Stay on basic word if moving back from translation
                }
            } else {
                if (!isTranslation) {
                    if (index > 0) newIndex = index - 1;
                    newIsTranslation = false;
                } else {
                    newIsTranslation = true; // Stay on translation if moving back from basic word
                }
            }
        } else { // 'next'
            if (isTranslation) {
                if (!translationFirst) { // If basic word was first, and now showing translation, next should be next word's basic
                    if (words.length > index + 1) newIndex = index + 1;
                }
                newIsTranslation = false;
                setDisplayWord(words[newIndex]?.basicWord || null);
            } else {
                if (translationFirst) { // If translation was first, and now showing basic, next should be next word's translation
                    if (words.length > index + 1) newIndex = index + 1;
                }
                newIsTranslation = true;
                setDisplayWord(words[newIndex]?.translation || null);
            }
        }

        setIndex(newIndex);
        setIsTranslation(newIsTranslation);

        // Update displayWord after setting index and isTranslation
        if (newIsTranslation) {
            setDisplayWord(words[newIndex]?.translation || null);
        } else {
            setDisplayWord(words[newIndex]?.basicWord || null);
        }
    };*/

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
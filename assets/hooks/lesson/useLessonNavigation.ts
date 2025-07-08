import { useState, useEffect, useCallback } from 'react';
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

interface NavigationState {
    newIndex: number;
    newIsTranslation: boolean;
    isValid: boolean; // Czy operacja nawigacji jest możliwa
}

const calculateNextState = (
    currentIdx: number,
    currentIsTranslation: boolean,
    direction: 'prev' | 'next',
    wordsLength: number,
    translationFirst: boolean
): NavigationState => {
    let newIndex = currentIdx;
    let newIsTranslation = currentIsTranslation;
    let isValid = true;

    if (direction === 'next') {
        if (currentIsTranslation) { // Obecnie wyświetlamy tłumaczenie, chcemy iść do następnego słowa
            if (currentIdx < wordsLength - 1) {
                newIndex = currentIdx + 1;
                newIsTranslation = translationFirst; // Dla nowego słowa startujemy od trybu translationFirst
            } else {
                isValid = false; // Jesteśmy na ostatnim słowie i jego tłumaczeniu, nie ma dalej
            }
        } else { // Obecnie wyświetlamy słowo, chcemy iść do jego tłumaczenia
            newIsTranslation = true;
        }
    } else { // direction === 'prev'
        if (currentIsTranslation) { // Obecnie wyświetlamy tłumaczenie, chcemy wrócić do słowa podstawowego
            newIsTranslation = false; // Po prostu zmieniamy widok na słowo podstawowe dla TEGO SAMEGO indeksu
        } else { // Obecnie wyświetlamy słowo podstawowe, chcemy cofnąć się do poprzedniego słowa
            if (currentIdx > 0) {
                newIndex = currentIdx - 1;
                newIsTranslation = false; // Po cofnięciu indeksu, zawsze wyświetlamy słowo podstawowe (PL)
            } else {
                isValid = false; // Jesteśmy na pierwszym słowie i jego podstawowej formie, nie ma dalej
            }
        }
    }

    return { newIndex, newIsTranslation, isValid };
};

export const useLessonNavigation = ({ words, translationFirst }: UseLessonNavigationProps): UseLessonNavigationResult => {
    const [index, setIndex] = useState(0);
    const [isTranslation, setIsTranslation] = useState(false);
    const [displayWord, setDisplayWord] = useState<string | null>(null);

    const updateDisplayWordBasedOnState = useCallback((currentIdx: number, currentIsTranslation: boolean) => {
        if (!words.length || currentIdx < 0 || currentIdx >= words.length) {
            setDisplayWord(null);
            return;
        }
        const currentWord = words[currentIdx];
        setDisplayWord(currentIsTranslation ? currentWord.translation : currentWord.basicWord);
    }, [words]);

    const lessonReset = useCallback(() => {
        setIndex(0);
        const initialIsTranslation = translationFirst;
        setIsTranslation(initialIsTranslation);
        updateDisplayWordBasedOnState(0, initialIsTranslation);
    }, [words, translationFirst, updateDisplayWordBasedOnState]);

    useEffect(() => {
        lessonReset();
    }, [words, translationFirst, lessonReset]);

    const handleShowWord = useCallback((direction: 'prev' | 'next') => {
        const { newIndex, newIsTranslation, isValid } = calculateNextState(
            index,
            isTranslation,
            direction,
            words.length,
            translationFirst
        );

        if (!isValid) {
            return;
        }

        setIndex(newIndex);
        setIsTranslation(newIsTranslation);
        updateDisplayWordBasedOnState(newIndex, newIsTranslation);

    }, [index, isTranslation, words, translationFirst, updateDisplayWordBasedOnState]);

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
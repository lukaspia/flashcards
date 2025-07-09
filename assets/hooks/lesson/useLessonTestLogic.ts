import { useState } from 'react';
import { Word } from '../../types/word.types';
import { Lesson } from '../../types/lesson.types';
import {useLessonApiActions} from "./useLessonTestLogicApiActions";

const processAnswer = (
    answer: boolean,
    currentWord: Word,
    nextRoundWords: Word[],
    setNextRoundWords: React.Dispatch<React.SetStateAction<Word[]>>,
    updateWordError: (wordId: number, increase: boolean) => void
) => {
    if (!answer) {
        setNextRoundWords([...nextRoundWords, currentWord]);
        updateWordError(currentWord.id, true);
    } else {
        updateWordError(currentWord.id, false);
    }
};

const getUpdatedWordWithErrors = (word: Word, increase: boolean): Word => {
    const currentErrors = word.errors || 0;
    const newErrors = increase ? currentErrors + 1 : Math.max(0, currentErrors - 1);
    return { ...word, errors: newErrors };
};

interface UseLessonTestLogicProps {
    words: Word[];
    wordsError: Word[];
    setWords: React.Dispatch<React.SetStateAction<Word[]>>;
    setWordsError: React.Dispatch<React.SetStateAction<Word[]>>;
    lesson: Lesson | undefined;
    index: number;
    isTranslation: boolean;
    translationFirst: boolean;
    handleShowWord: (direction: 'next') => void;
    handleLessonList: () => void;
}

interface UseLessonTestLogicResult {
    nextRoundWords: Word[];
    setNextRoundWords: React.Dispatch<React.SetStateAction<Word[]>>;
    round: number;
    setRound: React.Dispatch<React.SetStateAction<number>>;
    showSummary: boolean;
    setShowSummary: React.Dispatch<React.SetStateAction<boolean>>;
    lessonMessage: string;
    handleAnswer: (answer: boolean) => void;
    nextRound: () => void;
    handleSaveLesson: () => void;
}

export const useLessonTestLogic = ({
   words,
   wordsError,
   setWords,
   setWordsError,
   lesson,
   index,
   isTranslation,
   translationFirst,
   handleShowWord,
   handleLessonList,
}: UseLessonTestLogicProps): UseLessonTestLogicResult => {
    const [nextRoundWords, setNextRoundWords] = useState<Word[]>([]);
    const [round, setRound] = useState(1);
    const [showSummary, setShowSummary] = useState(false);

    const { lessonMessage, saveLesson } = useLessonApiActions(handleLessonList);

    const updateWordError = (wordId: number, increase: boolean = true) => {
        setWordsError(prevWordsError => prevWordsError.map(word => {
            if (word.id === wordId) {
                return getUpdatedWordWithErrors(word, increase);
            }
            return word;
        }));
    };

    const isEndOfRound = (): boolean => {
        const isLastWord = index >= (words.length - 1);
        const isCorrectDirectionForEnd = translationFirst ? !isTranslation : isTranslation;
        return isLastWord && isCorrectDirectionForEnd;
    };

    const handleAnswer = (answer: boolean) => {
        processAnswer(answer, words[index], nextRoundWords, setNextRoundWords, updateWordError);

        if (isEndOfRound()) {
            setShowSummary(true);
        }
        handleShowWord('next');
    };

    const nextRound = () => {
        setWords([...nextRoundWords]);
        setNextRoundWords([]);
        setShowSummary(false);
        setRound(prev => prev + 1);
    };

    const handleSaveLesson = () => {
        if (!lesson) return;

        const newLesson = {
            ...lesson,
            words: wordsError,
        } as Lesson;

        saveLesson(newLesson);
    };

    return {
        nextRoundWords,
        setNextRoundWords,
        round,
        setRound,
        showSummary,
        setShowSummary,
        lessonMessage,
        handleAnswer,
        nextRound,
        handleSaveLesson,
    };
};
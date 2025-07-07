import { useEffect, useState } from 'react';
import { Word } from '../../types/word.types';
import { Lesson } from '../../types/lesson.types';
import { updateLesson, getLessonMessage } from '../../services/api/lessonApi';

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
    const [lessonMessage, setLessonMessage] = useState('Gratulacje!');


    useEffect(() => {
        getLessonMessage().then(response => {
            if (response.data.message) {
                setLessonMessage(response.data.message);
            }
        });
    }, []);

    const updateWordError = (wordId: number, increase: boolean = true) => {
        const updatedWords = wordsError.map((word) => {
            if (word.id === wordId) {
                if (increase) {
                    return { ...word, errors: (word.errors || 0) + 1 };
                } else if ((word.errors || 0) > 0) {
                    return { ...word, errors: (word.errors || 0) - 1 };
                }
            }
            return word;
        });
        setWordsError(updatedWords);
    };

    const handleAnswer = (answer: boolean) => {
        if(!answer) {
            setNextRoundWords([...nextRoundWords, words[index]]);
            updateWordError(words[index].id);
        } else {
            updateWordError(words[index].id, false);
        }

        if(index >= ((words.length ?? 0) - 1) && (translationFirst ? isTranslation === false : isTranslation === true)) {
            setShowSummary(true);
        }

        handleShowWord('next');
    }


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

        updateLesson(newLesson)
            .then(() => {
                handleLessonList();
            })
            .catch((error) => {
                console.error(error);
            });
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
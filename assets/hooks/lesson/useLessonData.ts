import { useState, useEffect } from 'react';
import useLesson from '../useLesson';
import {Lesson} from '../../types/lesson.types';
import {Word} from '../../types/word.types';

interface UseLessonDataProps {
    lessonId: number;
}

interface UseLessonDataResult {
    lesson: Lesson | undefined;
    isLoading: boolean;
    words: Word[];
    setWords: React.Dispatch<React.SetStateAction<Word[]>>;
    wordsError: Word[];
    setWordsError: React.Dispatch<React.SetStateAction<Word[]>>;
}

export const useLessonData = ({ lessonId }: UseLessonDataProps): UseLessonDataResult => {
    const [lesson, isLoading] = useLesson(lessonId);
    const [words, setWords] = useState<Word[]>([]);
    const [wordsError, setWordsError] = useState<Word[]>([]);

    useEffect(() => {
        if (lesson?.words) {
            setWords([...lesson.words]);
            setWordsError([...lesson.words]);
        } else {
            setWords([]);
            setWordsError([]);
        }
    }, [lesson]);

    return { lesson, isLoading, words, setWords, wordsError, setWordsError };
};
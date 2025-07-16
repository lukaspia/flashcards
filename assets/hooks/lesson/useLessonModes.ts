import { useState, useEffect, useCallback } from 'react';
import { Word } from '../../types/word.types';
import { Lesson } from '../../types/lesson.types';
import shuffle from "../../utils/array-shuffler";

const getHardWords = (words: Word[]): Word[] => {
    return words.filter(word => word.errors > 0);
};

const getShuffledWords = (words: Word[]): Word[] => {
    return shuffle([...words]);
};

const useStudyModeState = () => {
    const [studyMode, setStudyMode] = useState<'learning' | 'testing'>('learning');
    const handleSwitchLearningProcess = useCallback(() => {
        setStudyMode(prev => (prev === 'learning' ? 'testing' : 'learning'));
    }, []);
    return { studyMode, setStudyMode, handleSwitchLearningProcess };
};

const useTranslationFirstState = () => {
    const [translationFirst, setTranslationFirst] = useState(false);
    const handleSwitchTranslationFirst = useCallback(() => {
        setTranslationFirst(prev => !prev);
    }, []);
    return { translationFirst, setTranslationFirst, handleSwitchTranslationFirst };
};

interface UseHardWordsModeStateProps {
    lesson: Lesson | undefined;
    setWords: React.Dispatch<React.SetStateAction<Word[]>>;
}

const useHardWordsModeState = ({ lesson, setWords }: UseHardWordsModeStateProps) => {
    const [hardWordsMode, setHardWordsMode] = useState(false);

    useEffect(() => {
        if (!lesson?.words) return;
        if (hardWordsMode) {
            setWords(getHardWords(lesson.words));
        } else {
            setWords([...lesson.words]);
        }
    }, [hardWordsMode, lesson?.words, setWords]);

    const handleSwitchHardWordsMode = useCallback(() => {
        setHardWordsMode(prev => !prev);
    }, []);

    return { hardWordsMode, setHardWordsMode, handleSwitchHardWordsMode };
};

interface UseMixingWordsStateProps {
    lesson: Lesson | undefined;
    setWords: React.Dispatch<React.SetStateAction<Word[]>>;
}

const useMixingWordsState = ({ lesson, setWords }: UseMixingWordsStateProps) => {
    const [mixingWords, setMixingWords] = useState(false);

    useEffect(() => {
        if (!lesson?.words) return;
        if (mixingWords) {
            setWords(prevWords => getShuffledWords(prevWords));
        } else {
            setWords([...lesson.words]);
        }
    }, [mixingWords, lesson?.words, setWords]);

    const handleSwitchMixingWords = useCallback(() => {
        setMixingWords(prev => !prev);
    }, []);

    return { mixingWords, setMixingWords, handleSwitchMixingWords };
};

interface UseLessonModesProps {
    initialWords: Word[];
    lesson: Lesson | undefined;
    setWords: React.Dispatch<React.SetStateAction<Word[]>>;
}

interface UseLessonModesResult {
    studyMode: 'learning' | 'testing';
    setStudyMode: React.Dispatch<React.SetStateAction<'learning' | 'testing'>>;
    translationFirst: boolean;
    setTranslationFirst: React.Dispatch<React.SetStateAction<boolean>>;
    mixingWords: boolean;
    setMixingWords: React.Dispatch<React.SetStateAction<boolean>>;
    hardWordsMode: boolean;
    setHardWordsMode: React.Dispatch<React.SetStateAction<boolean>>;
    handleSwitchTranslationFirst: () => void;
    handleSwitchLearningProcess: () => void;
    handleSwitchHardWordsMode: () => void;
    handleSwitchMixingWords: () => void;
}

export const useLessonModes = ({ initialWords, lesson, setWords }: UseLessonModesProps): UseLessonModesResult => {
    const { studyMode, setStudyMode, handleSwitchLearningProcess } = useStudyModeState();
    const { translationFirst, setTranslationFirst, handleSwitchTranslationFirst } = useTranslationFirstState();
    const { hardWordsMode, setHardWordsMode, handleSwitchHardWordsMode } = useHardWordsModeState({ lesson, setWords });
    const { mixingWords, setMixingWords, handleSwitchMixingWords } = useMixingWordsState({ lesson, setWords });

    return {
        studyMode,
        setStudyMode,
        translationFirst,
        setTranslationFirst,
        mixingWords,
        setMixingWords,
        hardWordsMode,
        setHardWordsMode,
        handleSwitchTranslationFirst,
        handleSwitchLearningProcess,
        handleSwitchHardWordsMode,
        handleSwitchMixingWords,
    };
};

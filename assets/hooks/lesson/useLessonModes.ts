import { useState, useEffect } from 'react';
import { Word } from '../../types/word.types';
import { Lesson } from '../../types/lesson.types';
import shuffle from "../../utils/array-shuffler";

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
    const [studyMode, setStudyMode] = useState<'learning' | 'testing'>('learning');
    const [translationFirst, setTranslationFirst] = useState(false);
    const [mixingWords, setMixingWords] = useState(false);
    const [hardWordsMode, setHardWordsMode] = useState(false);

    const handleSwitchTranslationFirst = () => {
        setTranslationFirst(prev => !prev);
    };

    const handleSwitchLearningProcess = () => {
        setStudyMode(prev => (prev === 'learning' ? 'testing' : 'learning'));
    };

    const handleSwitchHardWordsMode = () => {
        if (hardWordsMode) {
            if (lesson?.words) {
                setWords([...lesson.words]);
            }
            setHardWordsMode(false);
        } else {
            if (lesson?.words) {
                const wordsWithError = lesson.words.filter(word => word.errors > 0);
                setWords([...wordsWithError]);
            }
            setHardWordsMode(true);
        }
    };

    const handleSwitchMixingWords = () => {
        if (mixingWords) {
            if (lesson?.words) {
                setWords([...lesson.words]);
            }
            setMixingWords(false);
        } else {
            setWords(prevWords => shuffle([...prevWords]));
            setMixingWords(true);
        }
    };

    useEffect(() => {
    }, [translationFirst, studyMode, mixingWords, hardWordsMode]);

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

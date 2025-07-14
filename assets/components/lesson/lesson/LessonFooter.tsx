import React from 'react';
import {LessonPagination} from "./LessonPagination";
import {LessonOptions} from "./LessonOptions";

interface LessonFooterProps {
    studyMode: 'learning' | 'testing';
    translationFirst: boolean;
    isTranslation: boolean;
    hardWordsMode: boolean;
    mixingWords: boolean;
    currentIndex: number;
    totalWords: number;
    onHandleAnswer: (answer: boolean) => void;
    onHandleShowWord: (direction: 'prev' | 'next') => void;
    onLessonReset: () => void;
    onSwitchHardWordsMode: () => void;
    onSwitchLearningProcess: () => void;
    onSwitchMixingWords: () => void;
    onSwitchTranslationFirst: () => void;
}

export const LessonFooter: React.FC<LessonFooterProps> = ({
  studyMode,
  translationFirst,
  isTranslation,
  hardWordsMode,
  mixingWords,
  currentIndex,
  totalWords,
  onHandleAnswer,
  onHandleShowWord,
  onLessonReset,
  onSwitchHardWordsMode,
  onSwitchLearningProcess,
  onSwitchMixingWords,
  onSwitchTranslationFirst,
}) => {
    const isLastCardDisplay = currentIndex >= (totalWords - 1) && (translationFirst ? isTranslation === false : isTranslation === true);

    return (
        <div className="lesson-footer">
            <LessonPagination
                studyMode={studyMode}
                translationFirst={translationFirst}
                isTranslation={isTranslation}
                currentIndex={currentIndex}
                totalWords={totalWords}
                onHandleAnswer={onHandleAnswer}
                onHandleShowWord={onHandleShowWord}
            />
            <LessonOptions
                studyMode={studyMode}
                translationFirst={translationFirst}
                hardWordsMode={hardWordsMode}
                mixingWords={mixingWords}
                onLessonReset={onLessonReset}
                onSwitchHardWordsMode={onSwitchHardWordsMode}
                onSwitchLearningProcess={onSwitchLearningProcess}
                onSwitchMixingWords={onSwitchMixingWords}
                onSwitchTranslationFirst={onSwitchTranslationFirst}
            />
        </div>
    );
};
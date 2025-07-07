import React from 'react';
import { IconButton, Button } from '@mui/material';
import ArrowCircleLeftIcon from '@mui/icons-material/ArrowCircleLeft';
import ArrowCircleRightIcon from '@mui/icons-material/ArrowCircleRight';

interface LessonPaginationProps {
    studyMode: 'learning' | 'testing';
    translationFirst: boolean;
    isTranslation: boolean;
    currentIndex: number;
    totalWords: number;
    onHandleAnswer: (answer: boolean) => void;
    onHandleShowWord: (direction: 'prev' | 'next') => void;
}

export const LessonPagination: React.FC<LessonPaginationProps> = ({
  studyMode,
  translationFirst,
  isTranslation,
  currentIndex,
  totalWords,
  onHandleAnswer,
  onHandleShowWord,
}) => {
    const isLastCardDisplay = currentIndex >= (totalWords - 1) && (translationFirst ? isTranslation === false : isTranslation === true);
    const isAnswerSide = translationFirst ? isTranslation === false : isTranslation === true;

    if (studyMode === 'testing') {
        return (
            <div className="footer-pagination testing-mode">
                {isAnswerSide ? (
                    <div>
                        <div className="footer-buttons-helper">
                            Odpowiedź prawidłowa?
                        </div>
                        <div>
                            <Button className="btn button-false button-separator" variant="contained" onClick={() => onHandleAnswer(false)}>NIE</Button>
                            <Button className="btn button-true button-separator" variant="contained" onClick={() => onHandleAnswer(true)}>TAK</Button>
                        </div>
                    </div>
                ) : (
                    <div>
                        <div className="footer-buttons-helper"></div>
                        <div>
                            <Button className="btn button-primary" variant="contained" onClick={() => onHandleShowWord('next')}>Odpowiedź</Button>
                        </div>
                    </div>
                )}
            </div>
        );
    } else {
        return (
            <div className="footer-pagination learning-mode">
                <IconButton size="large" disabled={currentIndex < 1 && isTranslation === false}>
                    <ArrowCircleLeftIcon fontSize="large" className="basic-icon"
                                         onClick={() => onHandleShowWord('prev')} />
                </IconButton>
                <IconButton size="large" disabled={isLastCardDisplay}>
                    <ArrowCircleRightIcon fontSize="large" className="basic-icon"
                                          onClick={() => onHandleShowWord('next')} />
                </IconButton>
            </div>
        );
    }
};
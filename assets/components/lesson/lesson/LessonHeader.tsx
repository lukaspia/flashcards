import React from 'react';
import { IconButton, Grid } from '@mui/material';
import KeyboardReturnIcon from '@mui/icons-material/KeyboardReturn';

interface LessonHeaderProps {
    onReturnToList: () => void;
    studyMode: 'learning' | 'testing';
    nextRoundWordsCount: number;
    currentIndex: number;
    totalWords: number;
    round: number;
}

export const LessonHeader: React.FC<LessonHeaderProps> = ({
  onReturnToList,
  studyMode,
  nextRoundWordsCount,
  currentIndex,
  totalWords,
  round,
}) => {
    return (
        <div className="lesson-header">
            <Grid container spacing={2}>
                <Grid size={1}>
                    <IconButton onClick={onReturnToList}>
                        <KeyboardReturnIcon className="basic-icon" />
                    </IconButton>
                </Grid>
                <Grid size={2}>
                    {studyMode === 'testing' && (
                        <div>
                            Nieprawidłowo {nextRoundWordsCount}
                        </div>
                    )}
                </Grid>
                <Grid size={9}>
                    Słowo {currentIndex + 1} / {totalWords} runda {round}
                </Grid>
            </Grid>
        </div>
    );
};
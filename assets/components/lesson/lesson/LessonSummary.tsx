import React from 'react';
import { Button } from '@mui/material';
import { Word } from '../../../types/word.types';

interface LessonSummaryProps {
    totalWords: number;
    nextRoundWords: Word[];
    lessonMessage: string;
    onSaveLesson: () => void;
    onNextRound: () => void;
}

export const LessonSummary: React.FC<LessonSummaryProps> = ({
    totalWords,
    nextRoundWords,
    lessonMessage,
    onSaveLesson,
    onNextRound,
}) => {
    const correctAnswers = totalWords - nextRoundWords.length;
    const wrongAnswers = nextRoundWords.length;

    return (
        <div>
            <div>
                <table>
                    <thead>
                    <tr>
                        <th className="answer-correct">Prawidłowo</th>
                        <th className="answer-wrong">Nieprawidłowo</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{correctAnswers}</td>
                        <td>{wrongAnswers}</td>
                    </tr>
                    </tbody>
                </table>
                {wrongAnswers === 0 && (
                    <div>
                        <div className="section-separator">
                            {lessonMessage}
                        </div>
                        <div className="section-separator">
                            <Button className="button-primary" variant="contained" onClick={onSaveLesson}>Zapisz wynik i wróć do listy lekcji</Button>
                        </div>
                    </div>
                )}
            </div>
            <div>
                {wrongAnswers > 0 && (
                    <Button className="button-primary" variant="contained" onClick={onNextRound}>Kolejna runda</Button>
                )}
            </div>
        </div>
    );
};
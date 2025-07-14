import { useState, useEffect } from 'react';
import { getLessonMessage, updateLesson } from '../../services/api/lessonApi';
import { Lesson } from '../../types/lesson.types';

interface UseLessonApiActionsResult {
    lessonMessage: string;
    saveLesson: (lessonData: Lesson) => void;
    savingLesson: boolean;
    saveError: string | null;
}

export const useLessonApiActions = (handleLessonList: () => void): UseLessonApiActionsResult => {
    const [lessonMessage, setLessonMessage] = useState('Gratulacje!');

    const [savingLesson, setSavingLesson] = useState(false);
    const [saveError, setSaveError] = useState<string | null>(null);

    useEffect(() => {
        getLessonMessage().then(response => {
            if (response.data.message) {
                setLessonMessage(response.data.message);
            }
        });
    }, []);

    const saveLesson = (lessonData: Lesson) => {
        updateLesson(lessonData)
            .then(() => {
                handleLessonList();
            })
            .catch((error) => {
                console.error(error);
            });
    };

    return {
        lessonMessage,
        saveLesson,
        savingLesson,
        saveError,
    };
};
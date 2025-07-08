import { useState, useEffect } from 'react';
import {Lesson} from '../../types/lesson.types';
import { getLesson, updateLesson } from '../../services/api/lessonApi';

interface UseLessonEditDataProps {
    lessonId: number;
    onSaveSuccess?: () => void;
}

interface UseLessonDataResult {
    initialLesson: Lesson | undefined;
    editableLesson: Lesson | undefined;
    isLoading: boolean;
    isSaving: boolean;
    isError: boolean;
    setEditableLesson: (lesson: Lesson) => void;
    saveLesson: () => void;
}

export const useLessonEditData = ({
                                      lessonId,
                                      onSaveSuccess
                                  }: UseLessonEditDataProps): UseLessonDataResult => {
    const [initialLesson, setInitialLesson] = useState<Lesson | undefined>(undefined);
    const [editableLesson, setEditableLesson] = useState<Lesson | undefined>(undefined);
    const [isLoading, setIsLoading] = useState(true);
    const [isSaving, setIsSaving] = useState(false);
    const [isError, setIsError] = useState(false);

    useEffect(() => {
        if (lessonId) {
            setIsLoading(true);
            getLesson(lessonId)
                .then((result) => {
                    setInitialLesson(result.data.lesson);
                    setEditableLesson(result.data.lesson);
                })
                .catch(() => setIsError(true))
                .finally(() => setIsLoading(false));
        }
    }, [lessonId]);

    const saveLesson = () => {
        if (editableLesson) {
            setIsSaving(true);
            updateLesson(editableLesson)
                .then((result) => {
                    setEditableLesson(result.data.lesson);
                    if (onSaveSuccess) {
                        onSaveSuccess();
                    }
                })
                .catch((error) => console.error(error))
                .finally(() => setIsSaving(false));
        }
    };

    return { initialLesson, editableLesson, isLoading, isSaving, isError, setEditableLesson, saveLesson };
};
import { useState, useEffect } from 'react';
import {Lesson} from '../../types/lesson.types';
import { getLesson, updateLesson } from '../../services/api/lessonApi';
import axios from 'axios';

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
        if (!lessonId) {
            setIsLoading(false);
            return;
        }

        setIsLoading(true);
        const controller = new AbortController();

        getLesson(lessonId, { signal: controller.signal })
            .then((result) => {
                setInitialLesson(result.data.lesson);
                setEditableLesson(result.data.lesson);
            })
            .catch((error) => {
                if (!axios.isCancel(error)) {
                    console.error("Failed to fetch lesson:", error);
                    setIsError(true);
                }
            })
            .finally(() => setIsLoading(false));

        return () => {
            controller.abort();
        };
    }, [lessonId]);

    const saveLesson = () => {
        if (!editableLesson) {
            console.warn("No lesson to save.");
            return;
        }

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
    };

    return { initialLesson, editableLesson, isLoading, isSaving, isError, setEditableLesson, saveLesson };
};
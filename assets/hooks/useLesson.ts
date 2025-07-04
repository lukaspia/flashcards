import { useState, useEffect } from "react";
import {Lesson} from "../types/lesson.types";
import {getLesson} from "../services/api/lessonApi";
import axios from 'axios';

type LessonApiResponse = [
    Lesson | undefined,
    boolean,
    boolean,
];

export default function useLesson(id: number): LessonApiResponse {
    const [lesson, setLesson] = useState<Lesson>();
    const [isLoading, setIsLoading] = useState(false);
    const [isError, setIsError] = useState(false);

    useEffect(() => {
        const controller = new AbortController();

        setIsLoading(true);
        setIsError(false);
        setLesson(undefined);

        getLesson(id, { signal: controller.signal })
            .then(result => {
                setLesson(result.data.lesson);
            })
            .catch(error => {
                if (!axios.isCancel(error)) {
                    console.error("Failed to fetch lesson:", error);
                    setIsError(true);
                }
            })
            .finally(() => {
                setIsLoading(false);
            });

        return () => {
            controller.abort();
        };
    }, [id]);

    return [
        lesson,
        isLoading,
        isError,
    ];
}
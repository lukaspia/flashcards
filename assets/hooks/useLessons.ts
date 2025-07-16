import {useEffect, useState, useCallback} from "react";
import {Lesson} from "../types/lesson.types";
import {getLessons} from "../services/api/lessonApi";
import axios from 'axios';

type LessonApiResponse = [
    Lesson[],
    number,
    number,
    boolean,
    boolean,
    (page: number) => void,
    () => void
];

export default function useLessons(initialPage = 1): LessonApiResponse {
    const [lessons, setLessons] = useState<Lesson[]>([]);
    const [currentPage, setPage] = useState(initialPage);
    const [totalPages, setTotalPages] = useState(0);
    const [isLoading, setIsLoading] = useState(false);
    const [isError, setIsError] = useState(false);
    const [refreshTrigger, setRefreshTrigger] = useState(0);

    useEffect(() => {
        const controller = new AbortController();

        const fetchPaginatedLessons = () => {
            setIsLoading(true);
            setIsError(false);

            getLessons(currentPage, { signal: controller.signal })
                .then((result) => {
                    setLessons(result.data.lessons);
                    setTotalPages(result.data.totalPages);
                })
                .catch((error) => {
                    if (!axios.isCancel(error)) {
                        console.error("Failed to fetch lessons:", error);
                        setIsError(true);
                    }
                })
                .finally(() => {
                    setIsLoading(false);
                });
        };

        fetchPaginatedLessons();

        return () => {
            controller.abort();
        };
    }, [currentPage, refreshTrigger]);

    const refresh = useCallback(() => {
        setRefreshTrigger(prev => prev + 1);
    }, []);

    return [
        lessons,
        currentPage,
        totalPages,
        isLoading,
        isError,
        setPage,
        refresh
    ]
}
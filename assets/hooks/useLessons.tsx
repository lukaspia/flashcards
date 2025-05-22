import {useEffect, useState} from "react";
import {Lesson} from "../components/lesson/Lesson";
import {getLessons} from "../services/api/lessonApi";

type LessonApiResponse = [
    Lesson[],
    number,
    number,
    boolean,
    boolean,
    (page: number) => void
];

export default function useLessons(): LessonApiResponse {
    const [lessons, setLessons] = useState<Lesson[]>([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(0);
    const [isLoading, setIsLoading] = useState(false);
    const [isError, setIsError] = useState(false);

    useEffect(() => {
        fetchLessons();
    }, []);

    const fetchLessons = (page: number = 1) => {
        let isMounted = true;

        setIsLoading(true);
        setIsError(false);

        getLessons(page)
            .then((result) => {
                if (isMounted) {
                    setLessons(result.data.lessons);
                    setTotalPages(result.data.totalPages);
                    setCurrentPage(result.data.page);
                }
            })
            .catch((error) => {
                console.error(error);
                if (isMounted) {
                    setIsError(true);
                }
            })
            .finally(() => {
                if(isMounted) {
                    setIsLoading(false);
                }
            });

        return () => {
            isMounted = false;
        }
    }

    return [
        lessons,
        currentPage,
        totalPages,
        isLoading,
        isError,
        fetchLessons
    ]
}
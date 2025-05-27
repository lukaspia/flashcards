import { useState, useEffect } from "react";
import {Lesson} from "@/components/lesson/Lesson";
import {getLesson} from "../services/api/lessonApi";

type LessonApiResponse = [
    Lesson | undefined,
    boolean,
    boolean
];

export default function useLesson(id: number): LessonApiResponse {
    const [lesson, setLesson] = useState<Lesson>();
    const [isLoading, setIsLoading] = useState(false);
    const [isError, setIsError] = useState(false);

    useEffect(() => {
        fetchLesson(id);
    }, []);

    const fetchLesson = (id: number) => {
        let isMounted = true;

        setIsLoading(true);
        setIsError(false);

        getLesson(id).then(result => {
            if (isMounted) {
                setLesson(result.data.lesson);
            }
        }).catch(error => {
            console.error(error);
            if (isMounted) {
                setIsError(true);
            }
        }).finally(() => {
            if(isMounted) {
                setIsLoading(false);
            }
        })

        return () => {
            isMounted = false;
        }
    }

    return [
        lesson,
        isLoading,
        isError
    ];
}
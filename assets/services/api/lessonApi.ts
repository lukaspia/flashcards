import axios from "axios";
import {Lesson} from "../../types/lesson.types";

const BASE_URL = "/api/v1";

export async function getLessons(page: number = 1, options?: {signal: AbortSignal}) {
    return axios.get(`${BASE_URL}/lessons?page=` + page, {
        signal: options?.signal,
    }).then(res => res.data);
}

export async function getLesson(id: number, options?: {signal: AbortSignal}) {
    return axios.get(`${BASE_URL}/lessons/${id}`, {
        signal: options?.signal,
    }).then(res => res.data);
}

export async function addLesson(formData: FormData) {
    return axios.post(`${BASE_URL}/lessons`, formData).then(res => res.data);
}

export async function updateLesson(lesson: Lesson) {
    return axios.put(`${BASE_URL}/lessons`, lesson, {
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(res => res.data);
}

export async function removeLessons(lesson: Lesson) {
    return axios.delete(`${BASE_URL}/lessons/` + lesson.id).then(res => res.data);
}

export async function getLessonMessage() {
    return axios.get(`${BASE_URL}/lessons/message`).then(res => res.data);
}
import axios from "axios";
import {Lesson} from "@/components/lesson/Lesson";

const BASE_URL = "/api/v1";

export async function getLessons(page: number = 1) {
    return await axios.get(`${BASE_URL}/lessons?page=` + page).then(res => res.data);
}

export async function getLesson(id: number) {
    return await axios.get(`${BASE_URL}/lessons/${id}`).then(res => res.data);
}

export async function addLesson(formData: FormData) {
    return await axios.post(`${BASE_URL}/lessons`, formData).then(res => res.data);
}

export async function updateLesson(lesson: Lesson) {
    return await axios.put(`${BASE_URL}/lessons`, lesson, {
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(res => res.data);
}

export async function removeLessons(lesson: Lesson) {
    return await  axios.delete(`${BASE_URL}/lessons/` + lesson.id).then(res => res.data);
}

export async function getLessonMessage() {
    return await axios.get(`${BASE_URL}/lessons/message`).then(res => res.data);
}
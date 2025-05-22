import axios from "axios";
import {Lesson} from "@/components/lesson/Lesson";

const BASE_URL = "/api/v1";

export async function getLessons(page: number = 1) {
    return await axios.get(`${BASE_URL}/lessons?page=` + page).then(res => res.data);
}

export async function addLesson(formData: FormData) {
    return await axios.post(`${BASE_URL}/lesson`, formData).then(res => res.data);
}

export async function removeLessons(lesson: Lesson) {
    return await  axios.delete(`${BASE_URL}/lesson/` + lesson.id).then(res => res.data);
}
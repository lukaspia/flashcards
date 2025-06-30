import axios from "axios";

const BASE_URL = "/api/v1";

export async function translateWord(promptData: object) {
    return await axios.post(`${BASE_URL}/words/translate`, promptData).then(res => res.data);
}

export async function getCategories() {
    return await axios.get(`${BASE_URL}/words/categories`).then(res => res.data);
}
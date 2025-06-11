import axios from "axios";

const BASE_URL = "/api/v1";

export async function translateWord(promptData: object) {
    return await axios.post(`${BASE_URL}/word/translate`, promptData).then(res => res.data);
}
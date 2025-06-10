import axios from "axios";

const BASE_URL = "/api/v1";

export async function uploadImage(formData: FormData) {
    return await axios.post(`${BASE_URL}/image/upload`, formData).then(res => res.data);
}

export async function removeWordImage(wordId: number) {
    return await axios.delete(`${BASE_URL}/image/` + wordId).then(res => res.data);
}
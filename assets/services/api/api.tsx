import axios from "axios";

const BASE_URL = "/api/v1";

export async function uploadImage(formData: FormData) {
    return await axios.post(`${BASE_URL}/upload`, formData).then(res => res.data);
}
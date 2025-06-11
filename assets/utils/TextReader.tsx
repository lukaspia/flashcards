export const readText = (text: string, laguage = 'pl_PL'): void => {
    if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = laguage;

        window.speechSynthesis.speak(utterance);

        console.log(`Przeczytano: "${text}" w języku ${laguage}`);

    } else {
        console.warn("Your browser does not support Web Speech API (SpeechSynthesis).");
    }
};
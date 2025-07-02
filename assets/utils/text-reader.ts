const getAvailableVoices = (): SpeechSynthesisVoice[] => {
    return window.speechSynthesis.getVoices();
};

const findSuitableVoice = (voices: SpeechSynthesisVoice[], language: string): SpeechSynthesisVoice | undefined => {
    // Try to find a matching voice in order of preference
    return (
        voices.find(v => v.lang === language && v.name.toLowerCase().includes('female')) ||
        voices.find(v => v.lang === language) ||
        voices.find(v => v.lang.startsWith(language.split('-')[0]))
    );
};

const setupUtterance = (text: string, language: string, rate: number): SpeechSynthesisUtterance => {
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = language;
    utterance.rate = rate;
    utterance.pitch = 1;
    utterance.volume = 1;
    return utterance;
};

const speakWithVoice = (utterance: SpeechSynthesisUtterance, voice: SpeechSynthesisVoice): void => {
    utterance.voice = voice;
    window.speechSynthesis.speak(utterance);
};

const handleVoiceSelection = (utterance: SpeechSynthesisUtterance, language: string): void => {
    const synthesis = window.speechSynthesis;
    const voices = synthesis.getVoices();

    if (voices.length === 0) {
        console.warn('No voices available');
        return;
    }

    const voice = findSuitableVoice(voices, language) || voices[0];
    if (!voice) return;

    if (voice !== voices[0]) {
        console.warn(`No exact voice match for ${language}, using best available.`);
    }

    speakWithVoice(utterance, voice);
};

export const readText = (text: string, language = 'en-US', rate = 1): void => {
    if (!('speechSynthesis' in window)) {
        console.warn("Your browser does not support Web Speech API (SpeechSynthesis).");
        return;
    }

    const synthesis = window.speechSynthesis;
    synthesis.cancel();

    const utterance = setupUtterance(text, language, rate);
    const voices = getAvailableVoices();

    if (voices.length > 0) {
        handleVoiceSelection(utterance, language);
    } else {
        const onVoicesChanged = () => {
            synthesis.removeEventListener('voiceschanged', onVoicesChanged);
            handleVoiceSelection(utterance, language);
        };
        synthesis.addEventListener('voiceschanged', onVoicesChanged);
    }
};

export const stopSpeech = (): void => {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
    }
};
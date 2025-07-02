export const readText = (text: string, language = 'en-US', rate = 1): void => {
    if ('speechSynthesis' in window) {

        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = language;

        utterance.rate = rate;
        utterance.pitch = 1;
        utterance.volume = 1;

        const setAndSpeakVoice = () => {
            const voices = window.speechSynthesis.getVoices();

            let preferredVoice = voices.find(
                (voice) => voice.lang === language && voice.name.toLowerCase().includes('female')
            );

            if (!preferredVoice) {
                preferredVoice = voices.find((voice) => voice.lang === language);
            }

            if (!preferredVoice) {
                const langCode = language.split('-')[0];
                preferredVoice = voices.find((voice) => voice.lang.startsWith(langCode));
            }

            if (preferredVoice) {
                utterance.voice = preferredVoice;
            } else {
                console.warn(`No voice found for ${language}, using default.`);

                if (voices.length > 0) {
                    utterance.voice = voices[0];
                }
            }

            //utterance.onstart = () => console.log('Speech started');
            //utterance.onend = () => console.log('Speech ended');
            utterance.onerror = (event) => console.error('Speech error:', event);

            setTimeout(() => {
                window.speechSynthesis.speak(utterance);
            }, 100);
        };

        const voices = window.speechSynthesis.getVoices();
        if (voices.length > 0) {
            setAndSpeakVoice();
        } else {
            window.speechSynthesis.onvoiceschanged = () => {
                setAndSpeakVoice();
                window.speechSynthesis.onvoiceschanged = null;
            };
        }

    } else {
        console.warn("Your browser does not support Web Speech API (SpeechSynthesis).");
    }
};
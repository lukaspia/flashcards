import {useSortable} from "@dnd-kit/sortable";
import {CSS} from "@dnd-kit/utilities";
import React, {useContext} from "react";
import Grid from "@mui/material/Grid";
import {TextField} from "@mui/material";
import IconButton from "@mui/material/IconButton";
import ClearIcon from "@mui/icons-material/Clear";
import CloudUploadIcon from "@mui/icons-material/CloudUpload";
import PlaylistRemoveIcon from "@mui/icons-material/PlaylistRemove";
import {styled} from "@mui/material/styles";
import {Word} from "@/components/word/Word";
import WordsContext from "../../services/context/WordsContext";
import {uploadImage, removeWordImage} from "../../services/api/api";
import {translateWord} from "../../services/api/wordApi";

const VisuallyHiddenInput = styled('input')({
    clip: 'rect(0 0 0 0)',
    clipPath: 'inset(50%)',
    height: 1,
    overflow: 'hidden',
    position: 'absolute',
    bottom: 0,
    left: 0,
    whiteSpace: 'nowrap',
    width: 1,
});

interface WordRowProps {
    keyId: number;
    word: Word;
}

export default function WordRow({ keyId, word}: WordRowProps) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
    } = useSortable({ id: keyId });

    const {words, updateWords} = useContext(WordsContext);

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        padding: '10px',
        margin: '5px 0',
        border: '1px solid lightgray',
    };

    const handleUpdateWord = (key: number, field: keyof Word, value: any) => {
        const newWords = [...words];

        if(field !== 'id') {
            (newWords[key] as any)[field] = value;
        }

        updateWords(newWords);
    }

    const handleTranslateWord = (key: number, value: any) => {
        const promptData = {
            'word': value,
            'sourceLanguage': 'pl_PL',
            'targetLanguage': 'en_US',
        }

        translateWord(promptData).then(res => {
            console.log(res.data.translation);

            handleUpdateWord(key, 'translation', res.data.translation.translation);
            handleUpdateWord(key, 'example', res.data.translation.example);
        });
    }

    const handleRemoveWord = (idToRemove: number) => {
        const newWords = words.filter(word => word.id !== idToRemove);
        updateWords(newWords);
    }

    const handleUploadImage = (key: number, files: FileList | null, wordId: number) => {
        if(files && files.length > 0) {
            const file = files[0];

            const formData = new FormData();
            formData.append('image', file);
            formData.append('word', wordId as any as string);

            uploadImage(formData).then(res => {
                handleUpdateWord(key, 'image', res.data.image);
            });
        }
    }

    const handleRemoveWordImage = (key: number, wordId: number) => {
        removeWordImage(wordId).then(res => {
            handleUpdateWord(key, 'image', null);
        });
    }

    //TODO zrobić translatora z geminie a później zająć się głosami
    function przeczytajTekst(tekstDoPrzeczytania: any, jezyk = 'pl-PL') {
        // Sprawdź, czy przeglądarka obsługuje SpeechSynthesis
        if ('speechSynthesis' in window) {
            // Utwórz nowy obiekt SpeechSynthesisUtterance
            const utterance = new SpeechSynthesisUtterance(tekstDoPrzeczytania);

            // Ustaw język (np. polski)
            utterance.lang = jezyk;

            // Opcjonalne: Ustaw głos
            // Możesz pobrać listę dostępnych głosów:
            //const glosy = window.speechSynthesis.getVoices();
            //console.log(glosy);
            //utterance.voice = glosy.find(voice => voice.lang === jezyk && voice.name.includes('Polska'));
            // Pamiętaj, że dostępność głosów zależy od systemu operacyjnego użytkownika i przeglądarki.

            // Opcjonalne: Ustaw wysokość tonu (pitch, 0-2, domyślnie 1)
            // utterance.pitch = 1;

            // Opcjonalne: Ustaw szybkość mowy (rate, 0.1-10, domyślnie 1)
            // utterance.rate = 1;

            // Odtwórz tekst
            window.speechSynthesis.speak(utterance);

            console.log(`Przeczytano: "${tekstDoPrzeczytania}" w języku ${jezyk}`);

        } else {
            console.warn("Twoja przeglądarka nie obsługuje Web Speech API (SpeechSynthesis).");
            alert("Niestety, Twoja przeglądarka nie potrafi odtworzyć mowy.");
        }
    }

    return (
        <div key={word.id} className={`word-${word.id}`} ref={setNodeRef} style={style}>
            <Grid container spacing={2}>
                <Grid size={1}>
                    <div></div>
                </Grid>
                <Grid size={5}>
                    <div>
                        <TextField id="standard-basic" label="Nazwa pl" variant="standard" value={word.basicWord}
                                   onChange={(e) => {
                                       handleUpdateWord(keyId, 'basicWord', e.target.value);
                                       //przeczytajTekst("Cześć! To jest testowe zdanie po polsku.");
                                   }}
                                    onBlur={e => handleTranslateWord(keyId, e.target.value)}
                                    />
                    </div>
                </Grid>
                <Grid size={5}>
                    <div>
                        <TextField id="standard-basic" label="Nazwa en" variant="standard" value={word.translation}
                                   onChange={(e) => {
                                       handleUpdateWord(keyId, 'translation', e.target.value)
                                   }}/>
                    </div>
                </Grid>
                <Grid size={1}>
                    <div>
                        <div>{word.image &&
                            <div className="image-container">
                                <img src={word.image} alt="Word illustration" className="small-image"/>
                                <IconButton>
                                    <ClearIcon className="basic-icon"
                                               onClick={() => handleRemoveWordImage(keyId, word.id)}/>
                                </IconButton>
                            </div>
                        }
                        </div>
                        <div>
                            <IconButton component="label">
                                <CloudUploadIcon className="basic-icon"/>
                                <VisuallyHiddenInput
                                    type="file"
                                    onChange={(e) => {
                                        handleUploadImage(keyId, e.target.files, word.id);
                                    }}
                                    multiple
                                />
                            </IconButton>
                        </div>
                    </div>
                </Grid>
            </Grid>
            <Grid container spacing={2}>
                <Grid size={1}>
                    <div>
                        <button {...attributes} {...listeners} className="drag-handle">
                            ⠿
                        </button>
                        {keyId + 1}
                    </div>
                </Grid>
                <Grid size={5}>
                    <div>

                    </div>
                </Grid>
                <Grid size={5}>
                    <div>
                        <TextField
                            label="Przykład użycia"
                            multiline
                            rows={2}
                            variant="standard"
                            value={word.example}
                            onChange={(e) => {
                                handleUpdateWord(keyId, 'example', e.target.value)
                            }}
                        />
                    </div>
                </Grid>
                <Grid size={1}>
                    <div>
                        {keyId > 0 && (
                            <IconButton>
                                <PlaylistRemoveIcon className="basic-icon" onClick={() => handleRemoveWord(word.id)}/>
                            </IconButton>
                        )}
                    </div>
                </Grid>
            </Grid>
        </div>
    );
}
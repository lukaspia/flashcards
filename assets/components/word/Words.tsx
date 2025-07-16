import React, {useContext, useEffect} from "react";
import PlaylistAddIcon from "@mui/icons-material/PlaylistAdd";
import Button from "@mui/material/Button";
import Grid from '@mui/material/Grid';
import {Word} from "../../types/word.types";
import WordRow from "./WordRow";
import {
    DndContext,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import {
    arrayMove,
    SortableContext,
    sortableKeyboardCoordinates,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import WordsContext from "../../services/context/WordsContext";

export default function Words(): React.ReactElement {
    const {words, updateWords, sourceLanguage, targetLanguage} = useContext(WordsContext);

    const createEmptyWord = (): Word => ({
        id: Date.now(),
        basicWord: '',
        translation: '',
        example: '',
        image: '',
        wordCategory: '',
        errors: 0,
        color: '',
    });

    useEffect(() => {
        if (words.length === 0) {
            updateWords([createEmptyWord()]);
        }
    }, [words, updateWords]);

    const handleAddWord = () => {
        const newWords = [...words, createEmptyWord()];
        updateWords(newWords);
    }

    const sensors = useSensors(
        useSensor(PointerSensor),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );


    function handleDragEnd(event: any) {
        const {active, over} = event;

        if (!active || !over || active.id === over.id) {
            return;
        }

        const oldIndex = words.findIndex(word => word.id === active.id);
        const newIndex = words.findIndex(word => word.id === over.id);

        if (oldIndex === -1 || newIndex === -1) {
            console.warn("Error: Could not find word in array during drag end.", { activeId: active.id, overId: over.id, words });
            return;
        }

        const newWords = arrayMove(words, oldIndex, newIndex);
        updateWords(newWords);
    }

    return (
        <div className="lesson-words">
            <div>
                <Grid container spacing={2}>
                    <Grid size={2}>
                        <div>
                            <h5>Lista słów</h5>
                        </div>
                    </Grid>
                    <Grid size={10}>
                        <div>
                            {words.length}
                        </div>
                    </Grid>
                </Grid>
            </div>

            <Grid container spacing={2}>
                <Grid size={{ xs: 6, md: 6 }}>
                    <div>
                        <h6>Słowa {sourceLanguage}</h6>
                    </div>
                </Grid>
                <Grid size={{ xs: 6, md: 6 }}>
                    <div>
                        <h6>Słowa {targetLanguage}</h6>
                    </div>
                </Grid>
            </Grid>

            <DndContext
                sensors={sensors}
                collisionDetection={closestCenter}
                onDragEnd={handleDragEnd}
            >
                <SortableContext
                    items={words.map(word => word.id)}
                    strategy={verticalListSortingStrategy}
                >
                    {words.map((word, key) => (
                        <WordRow key={word.id} index={key} keyId={word.id} word={word} />
                    ))}
                </SortableContext>
            </DndContext>

            <Button
                className="btn button-primary"
                variant="contained"
                disabled={words.length > 29}
                onClick={handleAddWord}
                endIcon={<PlaylistAddIcon/>}>
                Dodaj
            </Button>
        </div>
    );
}
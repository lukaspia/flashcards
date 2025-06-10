import React, {useState} from "react";
import PlaylistAddIcon from "@mui/icons-material/PlaylistAdd";
import Button from "@mui/material/Button";
import Grid from '@mui/material/Grid';
import {Word} from "./Word";
import { styled } from '@mui/material/styles';
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
    useSortable,
    SortableContext,
    sortableKeyboardCoordinates,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';

interface WordsProps {
    updateWords: (words: Word[]) => void;
    words: Word[];
}

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

export default function Words({updateWords, words}: WordsProps): React.ReactElement {

    const emptyWord: Word = {
        id: Date.now(),
        basicWord: '',
        translation: '',
        example: '',
        image: ''
    };

    if(words.length == 0) {
        words.push(emptyWord);
    }

    const handleAddWord = () => {
        const newWords = [...words, emptyWord];
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

        if (active.id !== over.id) {

            const oldIndex = words.indexOf(active.id);
            const newIndex = words.indexOf(over.id);

            console.log(active.id, over.id);
            console.log(oldIndex, newIndex);

            const ar = arrayMove(words, over.id, active.id);

            updateWords(ar);

            /*updateWords((prevWords: Word[]) => {
                const oldIndex = prevWords.indexOf(active.id);
                const newIndex = prevWords.indexOf(over.id);

                return arrayMove(prevWords, oldIndex, newIndex);
            });*/
        }
    }


    return (
        <div className="lesson-words">
            <div>
                <Grid container spacing={2}>
                    <Grid size={2}>
                        <div>
                            <h2>Lista słów</h2>
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
                        <h3>Słowa PL</h3>
                    </div>
                </Grid>
                <Grid size={{ xs: 6, md: 6 }}>
                    <div>
                        <h3>Słowa EN</h3>
                    </div>
                </Grid>
            </Grid>


            <DndContext
                sensors={sensors}
                collisionDetection={closestCenter}
                onDragEnd={handleDragEnd}
            >
                <SortableContext
                    items={words}
                    strategy={verticalListSortingStrategy}
                >
                    {words.map((word, key) => (
                        <WordRow key={key} keyId={key} word={word} />
                    ))}
                </SortableContext>
            </DndContext>

            <Button
                className="btn btn-primary"
                variant="contained"
                disabled={words.length > 29}
                onClick={handleAddWord}
                endIcon={<PlaylistAddIcon/>}>
                Dodaj
            </Button>
        </div>
    );
}
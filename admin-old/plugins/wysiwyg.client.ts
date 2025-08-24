import { EditorState } from 'prosemirror-state';
import { EditorView } from 'prosemirror-view';
import { history, undo, redo } from 'prosemirror-history';
import { Schema } from 'prosemirror-model';
import { keymap } from 'prosemirror-keymap';
import {
  baseKeymap,
  toggleMark,
  chainCommands,
  newlineInCode,
  createParagraphNear,
  liftEmptyBlock,
  splitBlock,
} from 'prosemirror-commands';

import { sinkListItem, liftListItem } from 'prosemirror-schema-list';
import { defineNuxtPlugin } from '#app';

// Define your custom schema here
const mySchema = new Schema({
  nodes: {
    doc: { content: 'block+' },
    paragraph: {
      content: 'text*',
      group: 'block',
      toDOM() {
        return ['p', 0];
      },
      parseDOM: [{ tag: 'p' }],
    },
    text: { group: 'inline' },
    heading: {
      attrs: { level: { default: 1 } },
      content: 'text*',
      group: 'block',
      defining: true,
      toDOM(node) {
        return ['h' + node.attrs.level, 0];
      },
      parseDOM: [
        { tag: 'h1', attrs: { level: 1 } },
        { tag: 'h2', attrs: { level: 2 } },
        { tag: 'h3', attrs: { level: 3 } },
      ],
    },
    bullet_list: {
      content: 'list_item+',
      group: 'block',
      parseDOM: [{ tag: 'ul' }],
      toDOM() {
        return ['ul', 0];
      },
    },
    ordered_list: {
      content: 'list_item+',
      group: 'block',
      attrs: { order: { default: 1 } },
      parseDOM: [{ tag: 'ol' }],
      toDOM(node) {
        return node.attrs.order === 1 ? ['ol', 0] : ['ol', { start: node.attrs.order }, 0];
      },
    },
    list_item: {
      content: 'paragraph block*',
      parseDOM: [{ tag: 'li' }],
      toDOM() {
        return ['li', 0];
      },
    },
  },
  marks: {
    bold: {
      toDOM() {
        return ['strong', 0];
      },
      parseDOM: [{ tag: 'strong' }],
    },
    italic: {
      toDOM() {
        return ['em', 0];
      },
      parseDOM: [{ tag: 'em' }],
    },
    underline: {
      toDOM() {
        return ['u', 0];
      },
      parseDOM: [{ tag: 'u' }],
    },
  },
});

const customKeymap = keymap({
  Enter: chainCommands(newlineInCode, createParagraphNear, liftEmptyBlock, splitBlock),
  'Mod-b': toggleMark(mySchema.marks.bold),
  'Mod-i': toggleMark(mySchema.marks.italic),
  'Mod-u': toggleMark(mySchema.marks.underline),
  'Mod-z': undo,
  'Mod-y': redo,
  Tab: sinkListItem(mySchema.nodes.list_item),
  'Shift-Tab': liftListItem(mySchema.nodes.list_item),
});

export default defineNuxtPlugin(() => {
  return {
    provide: {
      createWysiwygEditor: (element: HTMLElement) => {
        const state = EditorState.create({
          schema: mySchema, // Ensure schema is correctly passed here
          plugins: [history(), customKeymap, keymap(baseKeymap)],
        });

        const view = new EditorView(element, {
          state,
          dispatchTransaction(transaction) {
            const newState = view.state.apply(transaction);
            view.updateState(newState);
          },
        });

        return {
          view,
          undo: () => undo(view.state, view.dispatch),
          redo: () => redo(view.state, view.dispatch),
        };
      },
    },
  };
});

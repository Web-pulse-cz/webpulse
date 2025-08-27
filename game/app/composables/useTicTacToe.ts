// composables/useTicTacToe.ts
import { ref, computed } from 'vue';

export type Cell = 'X' | 'O' | '';
export type Board = Cell[];

const WIN_LINES = [
  [0, 1, 2],
  [3, 4, 5],
  [6, 7, 8], // řádky
  [0, 3, 6],
  [1, 4, 7],
  [2, 5, 8], // sloupce
  [0, 4, 8],
  [2, 4, 6], // diagonály
];

export function useTicTacToe() {
  const board = ref<Board>(Array(9).fill(''));
  const turn = ref<'X' | 'O'>('X');
  const winner = ref<Cell>('');
  const moves = ref(0);

  const isFull = computed(() => moves.value >= 9);
  const isOver = computed(() => winner.value !== '' || isFull.value);

  function reset() {
    board.value = Array(9).fill('');
    turn.value = 'X';
    winner.value = '';
    moves.value = 0;
  }

  function checkWinner() {
    for (const [a, b, c] of WIN_LINES) {
      const va = board.value[a];
      if (va && va === board.value[b] && va === board.value[c]) {
        winner.value = va;
        return;
      }
    }
  }

  function playAt(index: number, symbol: 'X' | 'O') {
    if (board.value[index] || winner.value) return false;
    board.value[index] = symbol;
    moves.value++;
    checkWinner();
    if (!winner.value && !isFull.value) {
      turn.value = symbol === 'X' ? 'O' : 'X';
    }
    return true;
  }

  return {
    board,
    turn,
    winner,
    isFull,
    isOver,
    reset,
    playAt,
  };
}

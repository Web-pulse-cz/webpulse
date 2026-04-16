export interface Quiz {
  id: number;
  name: string;
  slug: string;
  tags: string;
  description: string;
  questions: QuizQuestion[];
}

export interface QuizQuestion {
  id: number;
  quiz_id: number;
  name: string;
  answers: QuizAnswer[];
}

export interface QuizAnswer {
  id: number;
  question_id: number;
  name: string;
  is_correct: boolean;
}

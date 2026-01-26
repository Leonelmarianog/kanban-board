export interface Label {
  name: string;
  color: string;
}

export interface Card {
  id: number;
  listId: number;
  content: string;
  labels: Label[];
}

export interface List {
  id: number;
  title: string;
  cards: Card[];
}

export interface Label {
  name: string;
  color: string;
}

export interface Card {
  id: number;
  content: string;
  labels: Label[];
}

export interface List {
  id: number;
  title: string;
  cards: Card[];
}

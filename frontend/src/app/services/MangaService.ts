import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root',
})
export class ServManga {
  constructor(private http: HttpClient) {}

monapi(){
return this.http.get('http://127.0.0.1:8000/manga/index').subscribe(console.log);
}
}

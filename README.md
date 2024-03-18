# FAQ 

### A Trainer-Student Forum built using React, PHP, Laravel, mySQL and Tailwind CSS.

## Features
**For Students:**
- Search Answers Archive
- Ask Questions

**Trainer:** 
- Search Questions
- Easily answer questions with prefilled form
- Create new posts (question and answer in one)

**All search functionality uses tokenization and query debouncing for higher quality searching, performance and UX.**

## User Authentication
- Out of the box Laravel Sanctum implementation using JS-Cookie on the frontend.

# App Links: 
### Front End Repo: https://github.com/shellycs50/faq-fe
### Back End Repo: https://github.com/shellycs50/faq-api
### Live Demo: https://faq-demo.robsheldrick.dev.io-academy.uk

## TODO: 
- Implement tanstack query and optimise backend fetching frequency (in progress)
- Create a clear separation between matched results and unmatched results (potentially remove non matches from UI)
- Find way to hide or remove dynamic navbar render delay (currently 'awaits' a cookies.get call on login) -> Quick Fix = loading screen or setting some global state as cookies feel hacky. 
- Mobile Layouts
- Investigate ways to achieve more efficient than current 0(n log n) time for searching. No issues with such a small dataset but a great practice problem.  

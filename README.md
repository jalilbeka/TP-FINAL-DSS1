# Decision Support System for University Specialization Selection
**University of Guelma — Department of Computer Science**  
**Module:** Decision Support Systems (DSS)  
**Final Practical Project (3rd Year ISIL - Academic Year 2025/2026)**

---

## 📌 Project Overview

Choosing a university specialization is a multi-criteria decision-making problem. This application is a web-based **Decision Support System (DSS)** that evaluates, ranks, and recommends university specializations for students using the **Weighted Sum Model (WSM)**.

### Features
- **Student Management:** Profiles, roles (Admin / Student), and accounts.
- **Specialization Management:** Codes (P1–P4), bilingual names (EN/FR), descriptions.
- **Criteria & Weighting:** Optimization direction (Maximize/Minimize) and weight configuration (sum = 1.00).
- **Evaluation Matrix:** Evaluation of specializations on a 1–10 scale.
- **Weighted Sum Model Engine:** Dynamic column min/max normalization, criteria weighting, tie detection, and ranking.
- **Visual Analytics:** Interactive Chart.js score bar charts and criteria radar comparison charts.
- **Bilingual Interface:** Instant EN/FR toggle with zero page reloads.
- **Dark Mode First:** Sleek, high-contrast, data-dashboard aesthetic.

---

## 🛠️ Technology Stack

| Layer | Technology |
|---|---|
| **Frontend** | HTML5, Tailwind CSS (CDN), Vanilla ES6+ JavaScript, Chart.js |
| **Backend** | PHP (Native PDO with prepared statements, RESTful JSON APIs) |
| **Database** | MySQL / MariaDB (Relational schema with foreign keys, checks, indexes) |
| **Design Standard** | UI/UX Pro Max Design Tokens (Contrast verified ≥ 4.5:1) |

---

## 📐 Mathematical Formulation (Weighted Sum Model)

Given:
- $m$ specializations $A_i$ ($i = 1, \dots, m$)
- $n$ criteria $C_j$ ($j = 1, \dots, n$)
- Raw performance matrix $X = [x_{ij}]$, where $x_{ij} \in [1, 10]$
- Weight vector $W = (w_1, w_2, \dots, w_n)$ where $\sum_{j=1}^n w_j = 1.00$

### 1. Normalization
Each criterion column is normalized based on its optimization direction:
- **For Maximization criteria (Beneficial):**
  $$r_{ij} = \frac{x_{ij}}{\max_k x_{kj}}$$
- **For Minimization criteria (Cost / Difficulty):**
  $$r_{ij} = \frac{\min_k x_{kj}}{x_{ij}}$$

### 2. Aggregation & Scoring
The overall score $S_i$ for specialization $A_i$ is computed as:
$$S_i = \sum_{j=1}^n w_j \cdot r_{ij}$$

### 3. Ranking & Tie Detection
- Specializations are sorted in descending order of $S_i$.
- If $S_a = S_b$, a **Tie** is flagged and highlighted in the UI and report.
- The highest-scoring specialization is flagged as the **Recommendation**.

---

## 🗄️ Database Schema & Entities

The relational database `dss_university` consists of 5 tables:
1. `users`: Authentication records, password hashes (`PASSWORD_BCRYPT`), and roles (`admin`, `student`).
2. `students`: Student profile details linked to user accounts.
3. `specializations`: Alternatives (P1 AI, P2 Cybersecurity, P3 Data Science, P4 ISSE).
4. `criteria`: Evaluation criteria with type (`maximize`/`minimize`) and weight.
5. `evaluations`: Student scores per specialization and criterion ($1.00 \le \text{score} \le 10.00$).

---


```

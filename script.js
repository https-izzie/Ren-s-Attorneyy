/* =====================================================
   RENS ATTORNEY — main.js
   External JavaScript file
   Link: <script src="main.js"></script>
   ===================================================== */


/* ── 1. NAVBAR: turns solid when user scrolls down ── */
const navbar = document.getElementById('navbar');

window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 60);
});


/* ── 2. MOBILE MENU toggle ── */
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}


/* ── 3. SCROLL REVEAL ANIMATIONS ──
   Elements with class "reveal" fade up into view
   as the user scrolls down the page.              */
const revealElements = document.querySelectorAll('.reveal');

const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, { threshold: 0.1 });

revealElements.forEach(el => revealObserver.observe(el));


/* ── 4. QUOTES CAROUSEL ──
   Rotates through 12 famous lawyer quotes.
   Changes automatically every 4 minutes (240,000 ms).
   User can also click the dots to jump to any quote. */

let quotes = [
    {
        text: "The good lawyer is not the man who qualifies all his qualifications, but who throws himself on your part so heartily that he can get you out of a scrape.",
        author: "Ralph Waldo Emerson"
    },
    {
        text: "It is better that ten guilty persons escape than that one innocent suffer.",
        author: "Sir William Blackstone"
    },
    {
        text: "The law is reason, free from passion.",
        author: "Aristotle"
    },
    {
        text: "Justice is the constant and perpetual will to allot to every man his due.",
        author: "Emperor Justinian I"
    },
    {
        text: "The safety of the people shall be the highest law.",
        author: "Marcus Tullius Cicero"
    },
    {
        text: "Injustice anywhere is a threat to justice everywhere.",
        author: "Martin Luther King Jr."
    },
    {
        text: "Wherever law ends, tyranny begins.",
        author: "John Locke"
    },
    {
        text: "In law, nothing is certain but the expense.",
        author: "Samuel Butler"
    },
    {
        text: "An unjust law is no law at all.",
        author: "Saint Augustine of Hippo"
    },
    {
        text: "The first duty of society is justice.",
        author: "Alexander Hamilton"
    },
    {
        text: "Lawyers are the only persons in whom ignorance of the law is not punished.",
        author: "Jeremy Bentham"
    },
    {
        text: "The law must be stable, but it must not stand still.",
        author: "Roscoe Pound"
    },
];

let currentQ    = 0;
const qText     = document.getElementById('quoteText');
const qAuthor   = document.getElementById('quoteAuthor');
const qDots     = document.getElementById('quoteDots');
const qProgress = document.getElementById('quoteProgress');

/* Build the clickable dot indicators */
function buildDots() {
    qDots.innerHTML = '';
    quotes.forEach((_, i) => {
        const dot = document.createElement('div');
        dot.className = 'q-dot' + (i === 0 ? ' active' : '');
        dot.setAttribute('aria-label', 'Quote ' + (i + 1));
        dot.addEventListener('click', () => goToQuote(i));
        qDots.appendChild(dot);
    });
}
buildDots();

/* Switch to a specific quote with fade animation */
function goToQuote(idx) {
    /* Fade out current quote */
    qText.classList.add('fading');
    qAuthor.classList.add('fading');

    /* Reset the progress bar animation */
    qProgress.classList.remove('running');
    void qProgress.offsetWidth; /* force reflow to restart animation */

    setTimeout(() => {
        /* Swap the text */
        currentQ            = idx;
        qText.textContent   = '\u201C' + quotes[idx].text + '\u201D';
        qAuthor.textContent = '\u2014 ' + quotes[idx].author;

        /* Fade back in */
        qText.classList.remove('fading');
        qAuthor.classList.remove('fading');

        /* Update which dot is active */
        document.querySelectorAll('.q-dot').forEach((d, i) => {
            d.classList.toggle('active', i === idx);
        });

        /* Restart the progress bar */
        qProgress.classList.add('running');

    }, 800); /* wait for fade-out to finish before swapping */
}

/* Start the progress bar on page load */
qProgress.classList.add('running');

/* Auto-rotate every 4 minutes = 240,000 milliseconds */
setInterval(() => {
    goToQuote((currentQ + 1) % quotes.length);
}, 240000);


/* ── 5. FILE UPLOAD ──
   Shows the attached file names inside the upload box.
   Also handles drag-and-drop onto the upload zone.   */

function onFileChange(input) {
    const fileList = document.getElementById('fileList');

    if (!input.files || input.files.length === 0) {
        fileList.textContent = '';
        return;
    }

    const names = Array.from(input.files)
        .map(f => '- ' + f.name)
        .join('\n');

    fileList.textContent = input.files.length + ' file(s) attached:\n' + names;
}

/* Drag-and-drop support for the upload zone */
const dropZone = document.getElementById('fileDropZone');

['dragenter', 'dragover'].forEach(evt => {
    dropZone.addEventListener(evt, (e) => {
        e.preventDefault();
        dropZone.classList.add('dragging');
    });
});

['dragleave', 'drop'].forEach(evt => {
    dropZone.addEventListener(evt, () => {
        dropZone.classList.remove('dragging');
    });
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    const fileInput   = document.getElementById('fileInput');
    fileInput.files   = e.dataTransfer.files;
    onFileChange(fileInput);
});


/* ── 6. FORM SUBMISSION ──
   Validates all required fields.
   If valid → shows the 24hr confirmation alert and resets the form.
   If invalid → highlights the empty fields in red.              */

async function handleSubmit() {
    const fields = [
        document.getElementById('firstName'),
        document.getElementById('lastName'),
        document.getElementById('email'),
        document.getElementById('phone'),
        document.getElementById('state'),
        document.getElementById('area'),
        document.getElementById('consultType'),
        document.getElementById('details'),
        document.getElementById('consent'),
    ];

    let allValid = true;

    fields.forEach(field => {
        const isEmpty = field.type === 'checkbox'
            ? !field.checked
            : !field.value.trim();

        if (isEmpty) {
            field.classList.add('error');
            allValid = false;
        } else {
            field.classList.remove('error');
        }
    });

    /* Stop here if anything is missing */
    if (!allValid) {
        alert('Please complete all required fields before submitting.');
        return;
    }
    try{ await sendToServer(); 
    } catch (err) {
        alert(err.message);
        return;
    }

    /* SUCCESS — show the 24hr confirmation alert */
    alert(
        'Your case has been submitted successfully!\n\n' +
        'We will get back to you in 24hrs...\n\n' +
        'Thank you for trusting Rens Attorney with your legal matter.\n' +
        'A confirmation will be sent to your email address shortly.\n\n' +
        '— The Rens Attorney Team\n' +
        '10 Optimum Hotel, Area 1, Garki, Abuja, FCT'
    );

    /* Clear the form after successful submission */
    document.getElementById('caseForm').reset();
    document.getElementById('fileList').textContent = '';
    fields.forEach(f => f.classList.remove('error'));
}
async function sendToServer() {
  const fd = new FormData();
  fd.append('first_name',    document.getElementById('firstName').value);
  fd.append('last_name',     document.getElementById('lastName').value);
  fd.append('email',        document.getElementById('email').value);
  fd.append('phone',        document.getElementById('phone').value);
  fd.append('state',        document.getElementById('state').value);
  fd.append('practice_area',document.getElementById('area').value);
  fd.append('consult_type', document.getElementById('consultType').value);
  fd.append('message',      document.getElementById('details').value);
  fd.append('consent',      document.getElementById('consent').checked ? '1' : '');

  const files = document.getElementById('fileInput').files;
  for (const f of files) {
    fd.append('documents[]', f);
  }

  const res  = await fetch('api/consult.php', { method: 'POST', body: fd });
  const data = await res.json();
  if (!res.ok) throw new Error((data.errors || [data.error]).join('\n'));
  return data.message;
}
/* ── DATABASE: fee, quotes, reviews ── */

function esc(text) {
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

async function loadFee() {
    try {
        const fee = await (await fetch('api/fees.php')).json();
        if (fee.ngn) document.getElementById('feeNgn').textContent = '₦' + fee.ngn.toLocaleString('en-NG');
        if (fee.usd) document.getElementById('feeUsd').textContent = '$' + fee.usd + ' USD';
    } catch (err) { /* keep the typed-in fee */ }
}

async function loadQuotes() {
    try {
        const data = await (await fetch('api/quotes.php')).json();
        if (Array.isArray(data) && data.length) {
            quotes = data.map(q => ({ text: q.text, author: q.author }));
            buildDots();
            goToQuote(0);
        }
    } catch (err) { /* keep the typed-in quotes */ }
}

async function loadReviews() {
    try {
        const data = await (await fetch('api/reviews.php')).json();
        if (!Array.isArray(data) || data.length === 0) return;

        document.getElementById('reviewsGrid').innerHTML = data.map(r => {
            const initials = r.name.split(' ').filter(Boolean).slice(0, 2)
                .map(w => w[0].toUpperCase()).join('');
            const stars = '★'.repeat(r.rating) + '☆'.repeat(5 - r.rating);
            return `
            <div class="review-card reveal visible">
                <div class="rc-stars">${stars}</div>
                <p class="rc-text">"${esc(r.comment)}"</p>
                <div class="rc-author">
                    <div class="rc-avatar">${esc(initials)}</div>
                    <div>
                        <div class="rc-name">${esc(r.name)}</div>
                        <div class="rc-case">${esc(r.case_info || '')}</div>
                    </div>
                    <div class="rc-verified">✓ Verified</div>
                </div>
            </div>`;
        }).join('');
    } catch (err) { /* keep the typed-in reviews */ }
}

async function submitReview() {
    const name    = document.getElementById('revName');
    const rating  = document.getElementById('revRating');
    const comment = document.getElementById('revComment');
    const caseInfo = document.getElementById('revCase');

    if (!name.value.trim() || !rating.value || !comment.value.trim()) {
        alert('Please enter your name, a rating and your review.');
        return;
    }

    const fd = new FormData();
    fd.append('name', name.value);
    fd.append('rating', rating.value);
    fd.append('comment', comment.value);
    fd.append('case_info', caseInfo.value);

    try {
        const res  = await fetch('api/reviews.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (!res.ok) throw new Error((data.errors || [data.error]).join('\n'));
        alert(data.message);
        document.getElementById('reviewForm').reset();
    } catch (err) {
        alert(err.message);
    }
}

loadFee();
loadQuotes();
loadReviews();
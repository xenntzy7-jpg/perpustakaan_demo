# Design Direction: Perpustakaan Digital

## Referensi

Spine's home, shop, and book-detail pages are the primary visual references. Apply their editorial bookstore language to real library content without copying the fictional shop's name, logo, writing, illustrations, prices, events, or commerce flows.

## Visual identity

- Navy and blue-teal frame the introduction and primary actions.
- Cool off-white and pale blue-gray keep book information readable.
- Teal is a restrained accent for important links and active states.
- Georgia or a system serif gives display headings a literary character; system sans-serif keeps interface text familiar and legible.
- A restrained cover shadow separates book imagery from its paper-colored frame.
- Use a text wordmark. Book-cover imagery comes only from covers uploaded for actual catalog records; missing covers use an explicit placeholder.

## Composition

- The public home page introduces the library and shows a short selection of real catalog records.
- The member dashboard makes the catalog the primary destination and keeps loan history, return requests, and logout available.
- Login, registration, member actions, book administration, and approval queues use the same library wordmark, navigation, palette, and typography.
- Administrative tables remain data-dense but gain responsive horizontal scrolling and clear row actions; data-entry forms use visible labels and grouped fields.
- The admin dashboard presents its four real tasks as equal-width action links in a two-column desktop grid that stacks on narrow screens; interface typography keeps those actions distinct from editorial headings.
- Standalone transaction errors and database status responses use the same page frame so users are not dropped into unstyled output.
- The member catalog uses book covers and concise metadata in a responsive grid, with search, category, availability, and sort controls sourced from existing data.
- Uploaded covers keep their original proportions and stay centered within consistent frames across catalog cards, book details, and administrative tables.
- Book detail shows only fields already present in the book record and links into the existing loan-confirmation flow.
- Larger section spacing separates the introduction from catalog records; tighter spacing keeps metadata and its cover grouped.
- Existing login, registration, administration, loan, return, and history behavior must remain intact.

## Interaction

- Navigation and actions use real destinations in the PHP application.
- Admins can attach PDF e-books to book records; only logged-in members can open the embedded reader and its protected PDF stream.
- The reader keeps the existing off-white, navy, and teal page frame; the large document viewport serves the reading task and keeps navigation back to the book visible.
- Search and catalog filters submit as GET parameters so their results remain available without a client framework.
- Every logout link asks for confirmation before ending the current session.
- Navigation and form submissions use a brief page transition and a restrained top loading indicator; the indicator stops when loading completes, and reduced-motion preferences disable the transitions.
- Hover is a restrained pointer affordance, not a required interaction. No continuous decorative animation is used.
- Empty results and database failures are communicated with text.

## Responsive behavior

- Navigation wraps at narrow widths.
- Book cards use one column on narrow screens, two columns as space allows, and additional columns only when their text remains readable.
- Home and member introductions stack when side-by-side content no longer fits.
- Detail imagery and book metadata stack on narrow screens and sit in columns on wider screens.
- Members read attached PDFs in an embedded reader so they can stay in the catalog context without a download action.
- No fixed navigation covers page content.

## Scope and evidence

The Spine reference informs visual language, not its fictional business facts or features. The current database provides title, author, publisher, publication year, category, stock, and an optional cover path. Do not invent prices, descriptions, reviews, staff picks, events, or book counts.

For this implementation, ENERGY is 2, RHYTHM is 2, and MOTION is 2. The energy comes from the editorial heading and dark introduction; the rhythm alternates an introduction, functional catalog controls, and book records; motion is limited to brief navigation feedback and simple hover states.

The owner approved a static, softly blurred Milky Way-like nebula in the dark home and member introductions as an atmospheric accent. It uses navy and blue-teal tones and does not animate, keeping the library content legible. The cool palette carries through shared navigation, forms, tables, messages, and footers so the visual refresh applies across the PHP pages. Buttons lift slightly on hover and press to confirm interaction without continuous motion.

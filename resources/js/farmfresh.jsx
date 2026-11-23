import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';

const photos = {
    eggs: 'https://images.unsplash.com/photo-1506976785307-8732e854ad03?auto=format&fit=crop&w=900&q=72',
    chicken: 'https://images.unsplash.com/photo-1587593810167-a84920ea0781?auto=format&fit=crop&w=900&q=72',
    chicks: 'https://images.unsplash.com/photo-1551986782-d0169b3f8fa7?auto=format&fit=crop&w=900&q=72',
    brownEggs: 'https://images.unsplash.com/photo-1518569656558-1f25e69d93d7?auto=format&fit=crop&w=900&q=72',
    farm: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1400&q=72',
    feed: 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=900&q=72',
};

const initialProducts = [
    { id: 1, name: 'Free-Range Eggs', detail: 'Tray of 30', price: 450, rating: '4.8', reviews: 126, distance: '3 km', freshness: 'Fresh today', type: 'Eggs', image: photos.eggs, seller: "Grace's Farm", note: 'Collected this morning' },
    { id: 2, name: 'Whole Chicken', detail: 'Approx. 1.8 kg', price: 720, rating: '4.6', reviews: 89, distance: '5 km', freshness: 'Halal', type: 'Meat', image: photos.chicken, seller: 'Kijani Poultry', note: 'Cleaned and chilled' },
    { id: 3, name: 'Day-old Chicks', detail: 'Box of 10', price: 1000, rating: '4.9', reviews: 54, distance: '8 km', freshness: 'Vaccinated', type: 'Chicks', image: photos.chicks, seller: 'Nakuru Hatchery', note: 'Healthy starter flock' },
    { id: 4, name: 'Organic Brown Eggs', detail: 'Pack of 12', price: 280, rating: '5.0', reviews: 31, distance: '4 km', freshness: 'Certified organic', type: 'Organic', image: photos.brownEggs, seller: 'Green Acres', note: 'Pasture raised' },
];

const initialOrders = [
    { id: 'ORD-1042', name: 'John M.', item: '30 eggs', amount: 450, status: 'Pending', time: '8:12 AM' },
    { id: 'ORD-1041', name: 'Mama Njeri Cafe', item: '20 broilers', amount: 9000, status: 'Paid', time: '7:46 AM' },
    { id: 'ORD-1039', name: 'Peter K.', item: '12 chicks', amount: 1200, status: 'In transit', time: 'Yesterday, 4:32 PM' },
];

const screens = [
    ['welcome', '👋', 'Choose a role'],
    ['farmer', '🧑‍🌾', 'Farmer home'],
    ['buyer-home', '🏠', 'Buyer home'],
    ['market', '🛒', 'Buyer marketplace'],
    ['supplier', '🚚', 'Supplier portal'],
    ['detail', '🥚', 'Product detail'],
    ['tracking', '📦', 'Order tracking'],
    ['chat', '💬', 'Chat & offers'],
    ['checkout', '💳', 'Checkout'],
    ['states', '🧰', 'Helpful states'],
    ['notifications', '🔔', 'Notifications'],
    ['profile', '👤', 'Profile'],
];

const roleHome = { Farmer: 'farmer', Buyer: 'buyer-home', Supplier: 'supplier' };

function money(value) {
    return `KES ${Number(value).toLocaleString('en-KE')}`;
}

function photosFor(item) {
    return [item.image, item.id === 1 ? photos.brownEggs : photos.farm, photos.farm];
}

function Card({ children, className = '' }) {
    return <section className={`rounded-[14px] border border-[#E8DEC9] bg-white shadow-[0_5px_18px_rgba(74,65,41,0.06)] ${className}`}>{children}</section>;
}

function Button({ children, onClick, kind = 'primary', className = '', ariaLabel, type = 'button' }) {
    const styles = {
        primary: 'bg-[#4A7C59] text-white hover:bg-[#3f6b4c] focus-visible:ring-[#4A7C59]',
        accent: 'bg-[#C96F4A] text-white hover:bg-[#ae5b3b] focus-visible:ring-[#C96F4A]',
        soft: 'border border-[#D8E4D9] bg-[#F1F6ED] text-[#315D3E] hover:bg-[#E5F0E3] focus-visible:ring-[#4A7C59]',
        outline: 'border border-[#DED5C1] bg-white text-[#34362F] hover:bg-[#FBF7EC] focus-visible:ring-[#4A7C59]',
        danger: 'border border-[#EBCBC4] bg-[#FFF2EF] text-[#A52F25] hover:bg-[#FCE5E0] focus-visible:ring-[#C0392B]',
    };
    return <button type={type} aria-label={ariaLabel} onClick={onClick} className={`inline-flex min-h-12 items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-extrabold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 ${styles[kind]} ${className}`}>{children}</button>;
}

function StatusChip({ status }) {
    const style = status === 'Paid' || status === 'Delivered' || status === 'Accepted'
        ? 'bg-[#E8F3E8] text-[#28633B]'
        : status === 'Pending' || status === 'Awaiting dispatch'
            ? 'bg-[#FFF3D7] text-[#875B08]'
            : status === 'In transit' || status === 'Out for delivery'
                ? 'bg-[#EAF1F8] text-[#365F81]'
                : 'bg-[#F3EEE2] text-[#575346]';
    const icon = status === 'Paid' || status === 'Delivered' || status === 'Accepted' ? '✓' : status === 'Pending' || status === 'Awaiting dispatch' ? '◷' : status === 'In transit' || status === 'Out for delivery' ? '↗' : '•';
    return <span className={`inline-flex min-h-8 items-center gap-1.5 rounded-full px-3 text-xs font-extrabold ${style}`}><span aria-hidden="true">{icon}</span>{status}</span>;
}

function AlertBanner({ children, tone = 'warning', action }) {
    const tones = {
        warning: 'border-[#E0A526]/35 bg-[#FFF5D9] text-[#624B1C]',
        error: 'border-[#C0392B]/25 bg-[#FFF0ED] text-[#7B261F]',
        success: 'border-[#3E8E5A]/25 bg-[#ECF6EE] text-[#28543A]',
        info: 'border-[#4A7C59]/20 bg-[#EFF5E9] text-[#315D3E]',
    };
    return <div role={tone === 'error' ? 'alert' : 'status'} className={`flex flex-col gap-3 rounded-[14px] border p-4 sm:flex-row sm:items-center sm:justify-between ${tones[tone]}`}><p className="text-sm font-bold leading-6">{children}</p>{action}</div>;
}

function TrustBadge({ children }) {
    return <span className="inline-flex min-h-8 items-center gap-1.5 rounded-full bg-[#EDF5EA] px-3 text-xs font-extrabold text-[#315D3E]"><span aria-hidden="true">✓</span>{children}</span>;
}

function StatCard({ icon, label, value, detail, tone = 'green' }) {
    const themes = {
        green: 'bg-[#F0F6ED] text-[#315D3E]',
        terra: 'bg-[#FBEEE7] text-[#9A4E32]',
        amber: 'bg-[#FFF5DD] text-[#805E14]',
        cream: 'bg-[#F8F3E7] text-[#514A3A]',
    };
    return <Card className="p-4 sm:p-5"><div className="flex items-start justify-between gap-3"><div><p className="text-sm font-bold text-[#656254]">{label}</p><p className="mt-2 text-[26px] font-black leading-tight tracking-tight text-[#2B2B2B]">{value}</p></div><span className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-xl ${themes[tone]}`} aria-hidden="true">{icon}</span></div><p className="mt-3 text-xs font-bold text-[#777263]">{detail}</p></Card>;
}

function QuantityStepper({ value, onChange, label = 'Quantity' }) {
    return <div className="inline-flex min-h-12 items-center gap-1 rounded-xl border border-[#DED5C1] bg-white p-1" aria-label={`${label}: ${value}`}>
        <button type="button" className="h-10 w-10 rounded-lg text-xl font-black text-[#4A7C59] hover:bg-[#EFF5E9] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#4A7C59]" aria-label={`Decrease ${label.toLowerCase()}`} onClick={() => onChange(Math.max(1, value - 1))}>−</button>
        <span className="w-8 text-center text-sm font-extrabold" aria-live="polite">{value}</span>
        <button type="button" className="h-10 w-10 rounded-lg text-xl font-black text-[#4A7C59] hover:bg-[#EFF5E9] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#4A7C59]" aria-label={`Increase ${label.toLowerCase()}`} onClick={() => onChange(value + 1)}>+</button>
    </div>;
}

function OrderRow({ order, onAccept, onDecline, onDispatch }) {
    return <div className="flex flex-col gap-3 border-b border-[#F0EBDD] py-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
        <div className="min-w-0"><div className="flex flex-wrap items-center gap-2"><strong className="text-sm font-black">{order.id}</strong><StatusChip status={order.status} /></div><p className="mt-1 text-sm font-bold text-[#504E44]">{order.name} · {order.item}</p><p className="mt-1 text-xs text-[#817C6D]">{order.time}</p></div>
        <div className="flex flex-wrap items-center gap-2"><strong className="mr-1 text-sm">{money(order.amount)}</strong>
            {order.status === 'Pending' && <><Button kind="soft" onClick={() => onAccept(order.id)}>Accept</Button><Button kind="danger" onClick={() => onDecline(order.id)}>Decline</Button></>}
            {(order.status === 'Paid' || order.status === 'Awaiting dispatch') && <Button kind="outline" onClick={() => onDispatch(order.id)}>{order.status === 'Paid' ? 'Arrange delivery' : 'Dispatch'}</Button>}
        </div>
    </div>;
}

function ProductCard({ product, onOpen, onAdd }) {
    return <Card className="group overflow-hidden transition hover:-translate-y-0.5 hover:shadow-[0_12px_28px_rgba(74,65,41,0.12)]">
        <button type="button" className="block w-full text-left focus-visible:outline focus-visible:outline-4 focus-visible:outline-[#4A7C59]" onClick={() => onOpen(product)} aria-label={`View ${product.name}`}>
            <div className="relative h-40 overflow-hidden bg-[#E9E1CE] sm:h-44"><img loading="lazy" decoding="async" src={product.image} alt={product.name} className="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]" /><span className="absolute left-3 top-3 rounded-full bg-[#FDF6E3]/95 px-3 py-1 text-xs font-black text-[#315D3E]">📍 {product.distance}</span></div>
            <div className="p-4"><div className="flex items-start justify-between gap-2"><div><h3 className="text-base font-black">{product.name}</h3><p className="mt-1 text-sm text-[#726E61]">{product.detail}</p></div><span className="shrink-0 rounded-full bg-[#F6F1E5] px-2.5 py-1 text-xs font-extrabold">⭐ {product.rating}</span></div><p className="mt-3 text-xs font-bold text-[#6F6A5D]">{product.seller} · {product.note}</p><p className="mt-3 flex items-center justify-between gap-2"><strong className="text-lg font-black">{money(product.price)}</strong><span className="text-xs font-bold text-[#4A7C59]">{product.freshness}</span></p></div>
        </button>
        <div className="px-4 pb-4"><Button kind="soft" className="w-full" onClick={() => onAdd(product)}>＋ Add to basket</Button></div>
    </Card>;
}

function Timeline({ items }) {
    return <ol className="space-y-0">{items.map((item, index) => <li key={item.title} className="relative flex gap-4 pb-6 last:pb-0">
        {index < items.length - 1 && <span className={`absolute left-[15px] top-8 h-[calc(100%-24px)] w-0.5 ${item.done ? 'bg-[#4A7C59]' : 'bg-[#E6DFD0]'}`} aria-hidden="true" />}
        <span className={`z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-black ${item.done ? 'bg-[#3E8E5A] text-white' : item.current ? 'border-2 border-[#4A7C59] bg-white text-[#4A7C59]' : 'bg-[#ECE8DE] text-[#8A867B]'}`} aria-hidden="true">{item.done ? '✓' : item.current ? '•' : '○'}</span>
        <div className="flex-1"><div className="flex flex-wrap items-center justify-between gap-2"><h3 className="text-sm font-extrabold">{item.title}</h3><span className="text-xs font-bold text-[#777263]">{item.time}</span></div>{item.detail && <p className="mt-1 text-sm text-[#6F6B60]">{item.detail}</p>}</div>
    </li>)}</ol>;
}

function ChatBubble({ who, children, time }) {
    const mine = who === 'Grace';
    return <div className={`flex ${mine ? 'justify-end' : 'justify-start'}`}><div className={`max-w-[85%] rounded-2xl px-4 py-3 ${mine ? 'rounded-br-sm bg-[#4A7C59] text-white' : 'rounded-bl-sm bg-[#F2EEE3] text-[#34362F]'}`}><p className={`mb-1 text-[11px] font-black ${mine ? 'text-white/75' : 'text-[#777263]'}`}>{who}</p><p className="text-sm font-semibold leading-6">{children}</p><p className={`mt-1 text-right text-[10px] ${mine ? 'text-white/70' : 'text-[#858071]'}`}>{time}</p></div></div>;
}

function BottomNav({ activeScreen, go, cartCount, role }) {
    const items = [[roleHome[role], '⌂', 'Home'], ['market', '⌕', 'Browse'], ['checkout', '🛒', `Cart (${cartCount})`], ['tracking', '▣', 'Orders'], ['profile', '♙', 'Profile']];
    return <nav aria-label="Main navigation" className="fixed inset-x-0 bottom-0 z-30 border-t border-[#E8DEC9] bg-[#FFFCF4]/95 px-2 pb-[max(env(safe-area-inset-bottom),0.5rem)] pt-2 shadow-[0_-5px_20px_rgba(74,65,41,0.07)] backdrop-blur md:hidden"><div className="mx-auto grid max-w-xl grid-cols-5 gap-1">{items.map(([screen, icon, label]) => <button key={screen} type="button" onClick={() => go(screen)} className={`flex min-h-12 flex-col items-center justify-center gap-0.5 rounded-xl text-[11px] font-extrabold ${activeScreen === screen ? 'bg-[#EDF5EA] text-[#315D3E]' : 'text-[#696657]'}`} aria-current={activeScreen === screen ? 'page' : undefined}><span className="text-lg leading-5" aria-hidden="true">{icon}</span><span>{label}</span></button>)}</div></nav>;
}

function EmptyState({ title, body, action }) {
    return <div className="rounded-[14px] border border-dashed border-[#D8CFBC] bg-[#FAF7EF] p-6 text-center"><span className="text-4xl" aria-hidden="true">🧺</span><h3 className="mt-2 text-base font-black">{title}</h3><p className="mx-auto mt-1 max-w-xs text-sm leading-6 text-[#716D62]">{body}</p>{action}</div>;
}

function SkeletonCard() {
    return <div aria-label="Loading fresh products" className="animate-pulse rounded-[14px] border border-[#E8DEC9] bg-white p-3"><div className="h-32 rounded-xl bg-[#EDE7D8]" /><div className="mt-3 h-4 w-2/3 rounded-full bg-[#EDE7D8]" /><div className="mt-2 h-3 w-1/2 rounded-full bg-[#F3EEE3]" /><div className="mt-4 h-10 rounded-xl bg-[#EDE7D8]" /></div>;
}

function Toast({ message, onClose }) {
    if (!message) return null;
    return <div role="status" aria-live="polite" className="fixed bottom-24 left-4 right-4 z-50 mx-auto flex max-w-md items-center justify-between gap-3 rounded-[14px] bg-[#2B2B2B] px-4 py-3 text-sm font-extrabold text-white shadow-xl md:bottom-6"><span>{message}</span><button type="button" className="min-h-10 min-w-10 rounded-lg text-lg" aria-label="Dismiss message" onClick={onClose}>×</button></div>;
}

function FarmFreshApp() {
    const [role, setRole] = useState('Buyer');
    const [screen, setScreen] = useState('welcome');
    const [cart, setCart] = useState({ 1: 2 });
    const [orders, setOrders] = useState(initialOrders);
    const [supplierOrders, setSupplierOrders] = useState([
        { id: 'SUP-2201', name: 'Grace Farm', item: '20 bags Layer Mash', amount: 36000, status: 'Awaiting dispatch', time: '9:01 AM' },
        { id: 'SUP-2199', name: 'Kariuki Poultry', item: '5 Gumboro vials', amount: 4500, status: 'Delivered', time: 'Yesterday, 2:16 PM' },
    ]);
    const [offline, setOffline] = useState(false);
    const [filter, setFilter] = useState('All');
    const [product, setProduct] = useState(initialProducts[0]);
    const [detailQuantity, setDetailQuantity] = useState(2);
    const [detailPhoto, setDetailPhoto] = useState(0);
    const [toast, setToast] = useState('');
    const [highContrast, setHighContrast] = useState(false);
    const [search, setSearch] = useState('');
    const [message, setMessage] = useState('');
    const [sentMessages, setSentMessages] = useState([]);
    const [paymentMethod, setPaymentMethod] = useState('M-Pesa');
    const [showNotifications, setShowNotifications] = useState(false);
    const [lastSynced] = useState('8:42 AM');

    const cartCount = Object.values(cart).reduce((sum, quantity) => sum + quantity, 0);
    const cartItems = useMemo(() => initialProducts.filter((item) => cart[item.id]).map((item) => ({ ...item, quantity: cart[item.id] })), [cart]);
    const cartSubtotal = cartItems.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const delivery = cartSubtotal > 0 ? 100 : 0;
    const visibleProducts = initialProducts.filter((item) => (filter === 'All' || (filter === 'Near me' ? Number.parseInt(item.distance, 10) <= 5 : item.type === filter)) && `${item.name} ${item.type} ${item.seller}`.toLowerCase().includes(search.toLowerCase()));

    useEffect(() => {
        const timer = toast ? window.setTimeout(() => setToast(''), 3600) : undefined;
        return () => window.clearTimeout(timer);
    }, [toast]);

    useEffect(() => {
        if ('serviceWorker' in navigator && window.location.protocol.startsWith('http')) {
            navigator.serviceWorker.register('/farmfresh-sw.js').catch(() => {});
        }
    }, []);

    function go(next) {
        setScreen(next);
        window.scrollTo({ top: 0, behavior: 'smooth' });
        setShowNotifications(false);
    }

    function switchRole(nextRole) {
        setRole(nextRole);
        go(roleHome[nextRole]);
    }

    function addToCart(item, quantity = 1) {
        setCart((current) => ({ ...current, [item.id]: (current[item.id] || 0) + quantity }));
        setToast(`${item.name} added to your basket`);
    }

    function changeOrderStatus(id, status) {
        setOrders((current) => current.map((item) => item.id === id ? { ...item, status } : item));
        setToast(status === 'Accepted' ? `✅ Order ${id} accepted — buyer notified` : `Order ${id} updated to ${status.toLowerCase()}`);
    }

    function changeSupplierStatus(id, status) {
        setSupplierOrders((current) => current.map((item) => item.id === id ? { ...item, status } : item));
        setToast(`Order ${id} marked as dispatched.`);
    }

    function openProduct(item) {
        setProduct(item);
        setDetailQuantity(item.id === 1 ? 2 : 1);
        setDetailPhoto(0);
        go('detail');
    }

    function navButton(id, icon, label) {
        return <button key={id} type="button" onClick={() => go(id)} className={`flex min-h-12 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-extrabold transition ${screen === id ? 'bg-[#EAF2E6] text-[#315D3E]' : 'text-[#5F5B50] hover:bg-[#F5F0E4]'}`} aria-current={screen === id ? 'page' : undefined}><span className="w-7 text-center text-lg" aria-hidden="true">{icon}</span>{label}</button>;
    }

    const content = (() => {
        if (screen === 'welcome') return <div className="mx-auto max-w-3xl py-5 sm:py-10">
            <div className="relative overflow-hidden rounded-[24px] bg-[#4A7C59] p-6 text-white shadow-[0_14px_32px_rgba(74,124,89,0.18)] sm:p-10">
                <img src={photos.farm} alt="" aria-hidden="true" className="absolute inset-0 h-full w-full object-cover opacity-20" />
                <div className="relative"><span className="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-extrabold">🌿 Local food, stronger farms</span><h1 className="mt-5 text-3xl font-black leading-tight sm:text-4xl">Welcome to FarmFresh 🐔</h1><p className="mt-2 text-xl font-extrabold">Who are you?</p><p className="mt-3 max-w-lg text-sm leading-6 text-white/90">Choose your space. Find the right tools to sell, shop, or supply your poultry community.</p></div>
            </div>
            <div className="mt-5 grid gap-3 sm:grid-cols-3">{[['Farmer', '🧑‍🌾', 'Sell eggs, birds, chicks, and meat', 'farmer'], ['Buyer', '🛒', 'Shop fresh poultry near you', 'market'], ['Supplier', '🚚', 'Supply feed, medicine, and services', 'supplier']].map(([title, icon, text, next]) => <button key={title} type="button" onClick={() => switchRole(title)} className="group flex min-h-40 flex-col items-start rounded-[14px] border border-[#E8DEC9] bg-white p-5 text-left shadow-[0_5px_18px_rgba(74,65,41,0.06)] transition hover:-translate-y-1 hover:border-[#4A7C59] hover:shadow-lg focus-visible:outline focus-visible:outline-4 focus-visible:outline-[#4A7C59]"><span className="text-3xl" aria-hidden="true">{icon}</span><span className="mt-3 text-lg font-black">I'm a {title}</span><span className="mt-1 text-sm leading-5 text-[#6F6B60]">{text}</span><span className="mt-4 text-sm font-black text-[#4A7C59]">Continue →</span></button>)}</div>
            <p className="mt-6 text-center text-sm font-bold text-[#6F6B60]">Already have an account? <button type="button" onClick={() => setToast('FarmFresh sign-in is not connected in this demo yet.')} className="min-h-12 px-2 font-black text-[#4A7C59] underline underline-offset-2">Log in</button></p>
        </div>;

        if (screen === 'farmer') return <div className="space-y-6">
            <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p className="text-sm font-extrabold text-[#4A7C59]">SATURDAY, NOVEMBER 21 · NAKURU</p><h1 className="mt-2 text-2xl font-black sm:text-3xl">Good morning, Grace 👋</h1><p className="mt-1 text-sm font-bold text-[#726E61]">Nakuru Farm · Your farm is growing well.</p></div><Button kind="outline" onClick={() => setShowNotifications((shown) => !shown)}>🔔 3 notifications</Button></div>
            {showNotifications && <NotificationsCard />}
            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"><StatCard icon="🥚" label="Eggs today" value="412" detail="▲ 8% from yesterday" /><StatCard icon="🐔" label="Live birds" value="186" detail="12 ready to sell" tone="terra" /><StatCard icon="💰" label="Unpaid orders" value={money(14500)} detail="4 invoices to follow up" tone="cream" /><StatCard icon="🌾" label="Feed stock" value="62 kg" detail="⚠ Reorder in 3 days" tone="amber" /></div>
            <AlertBanner tone="warning" action={<Button kind="accent" onClick={() => go('chat')}>Book vet</Button>}>🐔 Newcastle vaccination due for Flock B in 2 days. Book a vet to keep your birds protected.</AlertBanner>
            <Card className="p-4 sm:p-6"><div className="flex flex-wrap items-start justify-between gap-3"><div><h2 className="text-lg font-black">Orders to take care of</h2><p className="mt-1 text-sm text-[#777263]">Three recent orders · last updated 8:42 AM</p></div><Button kind="outline" onClick={() => go('tracking')}>View all orders</Button></div><div className="mt-2">{orders.map((order) => <OrderRow key={order.id} order={order} onAccept={(id) => changeOrderStatus(id, 'Accepted')} onDecline={(id) => changeOrderStatus(id, 'Declined')} onDispatch={(id) => changeOrderStatus(id, 'In transit')} />)}</div></Card>
            <div className="grid grid-cols-2 gap-3 sm:grid-cols-4"><Button onClick={() => go('market')}>＋ List product</Button><Button kind="soft" onClick={() => go('tracking')}>📦 Orders</Button><Button kind="soft" onClick={() => go('chat')}>💬 Messages</Button><Button kind="soft" onClick={() => go('states')}>📊 Reports</Button></div>
        </div>;

        if (screen === 'buyer-home') return <div className="space-y-6">
            <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p className="text-sm font-extrabold text-[#4A7C59]">SATURDAY, NOVEMBER 21 · NAKURU</p><h1 className="mt-2 text-2xl font-black sm:text-3xl">Good morning, Asha 👋</h1><p className="mt-1 text-sm font-bold text-[#726E61]">Your trusted farms have fresh picks for you today.</p></div><Button onClick={() => go('market')}>🔍 Browse fresh food</Button></div>
            <div className="grid gap-3 sm:grid-cols-3"><StatCard icon="📦" label="Orders this month" value="6" detail="Last order: Nov 21 at 8:02 AM" /><StatCard icon="⭐" label="FarmFresh rewards" value="240 pts" detail="60 points to your next KES 100 reward" tone="terra" /><StatCard icon="🚚" label="Next delivery" value="10:30 AM" detail="ORD-1041 · Rider James is 2.1 km away" tone="cream" /></div>
            <AlertBanner tone="success" action={<Button kind="soft" onClick={() => go('tracking')}>Track order</Button>}>Your fresh order is on its way! James is nearby, and your poultry is being kept cool.</AlertBanner>
            <div><div className="mb-3 flex items-end justify-between gap-3"><div><h2 className="text-lg font-black">Fresh picks near you</h2><p className="mt-1 text-sm text-[#777263]">Loved by families around Nakuru</p></div><button type="button" className="min-h-12 font-black text-[#4A7C59]" onClick={() => go('market')}>See all →</button></div><div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">{initialProducts.slice(0, 4).map((item) => <ProductCard key={item.id} product={item} onOpen={openProduct} onAdd={addToCart} />)}</div></div>
            <Card className="flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-center sm:p-5"><div><p className="text-sm font-extrabold text-[#4A7C59]">YOUR LOCAL FARM</p><h2 className="mt-1 text-base font-black">Grace's Farm · 3 km away</h2><p className="mt-1 text-sm text-[#777263]">Verified farmer · 4.9 stars · Fresh eggs collected today</p></div><Button kind="outline" onClick={() => openProduct(initialProducts[0])}>Shop Grace's farm</Button></Card>
        </div>;

        if (screen === 'market') return <div className="space-y-5">
            <div className="relative overflow-hidden rounded-[24px] bg-[#4A7C59] p-6 text-white sm:p-8"><img src={photos.farm} alt="" aria-hidden="true" className="absolute inset-0 h-full w-full object-cover opacity-20" /><div className="relative"><p className="text-sm font-extrabold text-white/85">FROM FARMS AROUND NAKURU</p><h1 className="mt-2 max-w-xl text-2xl font-black sm:text-3xl">Fresh poultry, from people you can trust.</h1><p className="mt-2 text-sm text-white/90">Good food. Fair prices. Grown close to home.</p></div></div>
            <label className="flex min-h-14 items-center gap-3 rounded-[14px] border border-[#DED5C1] bg-white px-4 shadow-sm"><span className="text-xl" aria-hidden="true">🔍</span><span className="sr-only">Search eggs, chicken, chicks</span><input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Search eggs, chicken, chicks…" className="min-w-0 flex-1 border-0 p-0 text-base font-bold placeholder:text-[#898474] focus:ring-0" /><button type="button" className="min-h-12 px-2 text-sm font-black text-[#4A7C59]" onClick={() => setToast('You can also tap the microphone on your keyboard to speak your search.')}>🎙 Speak</button></label>
            <div className="flex gap-2 overflow-x-auto pb-1" role="group" aria-label="Filter products">{['All', 'Eggs', 'Meat', 'Chicks', 'Organic', 'Near me'].map((item) => <button key={item} type="button" onClick={() => setFilter(item)} aria-pressed={filter === item} className={`min-h-12 shrink-0 rounded-full px-4 text-sm font-extrabold ${filter === item ? 'bg-[#4A7C59] text-white' : 'border border-[#DED5C1] bg-white text-[#59564B]'}`}>{item === 'Eggs' ? '🥚 ' : item === 'Meat' ? '🍗 ' : item === 'Chicks' ? '🐤 ' : item === 'Organic' ? '🌿 ' : item === 'Near me' ? '📍 ' : ''}{item}</button>)}</div>
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">{visibleProducts.map((item) => <ProductCard key={item.id} product={item} onOpen={openProduct} onAdd={addToCart} />)}{visibleProducts.length === 0 && <div className="sm:col-span-2 xl:col-span-4"><EmptyState title="No fresh finds yet" body="Try another search or choose a different category." action={<Button kind="soft" className="mt-4" onClick={() => { setSearch(''); setFilter('All'); }}>Show all products</Button>} /></div>}</div>
            <div className="grid gap-2 rounded-[14px] border border-[#DFE8D8] bg-[#F0F6ED] p-4 text-sm font-extrabold text-[#315D3E] sm:grid-cols-3"><span>✅ Verified local farms</span><span>🚚 2-hour delivery in town</span><span>🔄 Freshness guarantee</span></div>
        </div>;

        if (screen === 'supplier') return <div className="space-y-6">
            <div className="flex flex-col justify-between gap-4 rounded-[20px] bg-[#4A7C59] p-5 text-white sm:flex-row sm:items-center sm:p-7"><div><p className="text-sm font-bold text-white/80">SUPPLIER PORTAL</p><h1 className="mt-1 text-2xl font-black">FeedMaster Supplies</h1><p className="mt-1 text-sm text-white/90">Your trusted partner to 48 farms in Nakuru County</p></div><div className="rounded-[14px] bg-white/15 px-4 py-3"><p className="text-xs font-bold text-white/80">CREDIT LIMIT</p><p className="text-xl font-black">{money(250000)}</p></div></div>
            <div className="grid gap-3 sm:grid-cols-2"><AlertBanner tone="error" action={<Button kind="accent" onClick={() => setToast('Restock request started for 10 bags of Layer Mash.')}>Restock</Button>}>🔴 Layer Mash 50kg: 4 bags left. Reorder 10 before stock runs out.</AlertBanner><AlertBanner tone="warning">🟡 Gumboro vaccine: 18 vials expire in 40 days. Sell oldest stock first.</AlertBanner></div>
            <Card className="p-4 sm:p-6"><div className="flex items-center justify-between gap-3"><div><h2 className="text-lg font-black">Recent orders</h2><p className="mt-1 text-sm text-[#777263]">New requests from farms</p></div><Button kind="soft" onClick={() => setToast('Showing all 12 supplier orders.')}>All orders</Button></div><div className="mt-2">{supplierOrders.map((order) => <OrderRow key={order.id} order={order} onAccept={() => {}} onDecline={() => {}} onDispatch={(id) => changeSupplierStatus(id, 'In transit')} />)}</div></Card>
            <Card className="flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-center sm:p-5"><div><p className="text-sm font-extrabold text-[#4A7C59]">RECURRING ORDER</p><h2 className="mt-1 text-base font-black">Grace Farm — 10 bags per month</h2><p className="mt-1 text-sm text-[#777263]">Next delivery: December 3 · 10:00–12:00</p></div><Button kind="outline" onClick={() => setToast('Recurring order settings opened.')}>Edit plan</Button></Card>
            <Card className="overflow-hidden"><div className="p-4 sm:p-5"><h2 className="text-lg font-black">Delivery area</h2><p className="mt-1 text-sm text-[#777263]">6 farms in your zone · Nakuru Central</p></div><div className="relative h-48 overflow-hidden bg-[#E8EEDC]"><img src={photos.farm} alt="Green farmland around the Nakuru delivery area" className="h-full w-full object-cover opacity-50" /><div className="absolute inset-0 flex items-center justify-center"><div className="rounded-[14px] border border-white/80 bg-white/90 p-3 text-center shadow"><p className="text-sm font-black">📍 Nakuru delivery zone</p><p className="mt-1 text-xs font-bold text-[#656254]">6 green farm pins · 1 outside zone</p><p className="mt-2 text-xs">🟢 Grace Farm · 🟢 Kariuki Poultry · 🟢 4 more · ⚪ Rift Valley Coop</p></div></div></div></Card>
        </div>;

        if (screen === 'detail') return <div className="pb-20">
            <button type="button" className="mb-4 min-h-12 font-extrabold text-[#4A7C59]" onClick={() => go('market')}>← Back to fresh finds</button>
            <Card className="overflow-hidden"><div className="grid lg:grid-cols-[1.1fr_0.9fr]"><div className="relative min-h-64 bg-[#E8E1D2] lg:min-h-[490px]"><img src={photosFor(product)[detailPhoto]} alt={`${product.name} from ${product.seller}, photo ${detailPhoto + 1} of 3`} className="absolute inset-0 h-full w-full object-cover" /><div className="absolute left-4 top-4 rounded-full bg-[#2B2B2B]/85 px-4 py-2 text-sm font-extrabold text-white">🥚 Collected 6 hrs ago</div><div className="absolute bottom-4 left-0 right-0 flex justify-center gap-2" aria-label={`Photo ${detailPhoto + 1} of 3`}>{[0, 1, 2].map((index) => <span key={index} className={`h-2.5 rounded-full ${detailPhoto === index ? 'w-8 bg-white' : 'w-2.5 bg-white/65'}`} />)}</div><div className="absolute inset-x-0 bottom-0 flex justify-between px-3 pb-14"><button type="button" className="flex h-12 w-12 items-center justify-center rounded-full bg-white/90 text-xl" aria-label="Previous product photo" onClick={() => setDetailPhoto((index) => (index + 2) % 3)}>‹</button><button type="button" className="flex h-12 w-12 items-center justify-center rounded-full bg-white/90 text-xl" aria-label="Next product photo" onClick={() => setDetailPhoto((index) => (index + 1) % 3)}>›</button></div></div>
                <div className="p-5 sm:p-7"><p className="text-sm font-black uppercase tracking-wide text-[#4A7C59]">{product.type} · FARM FRESH</p><h1 className="mt-2 text-2xl font-black">{product.name} — {product.detail}</h1><p className="mt-2 text-2xl font-black">{money(product.price)} <span className="text-sm font-bold text-[#777263]">per tray</span></p><p className="mt-2 text-sm font-extrabold">⭐ {product.rating} · {product.reviews} reviews · <span className="text-[#3E8E5A]">98% arrived fresh</span></p><div className="mt-4 flex flex-wrap gap-2"><TrustBadge>Verified farm</TrustBadge><TrustBadge>Freshness promise</TrustBadge></div><div className="mt-5 rounded-xl bg-[#F8F4EA] p-4"><p className="font-black">👩‍🌾 Grace's Farm, Nakuru</p><p className="mt-1 text-sm font-bold text-[#656254]">✅ Verified · 4.9★ · Farming for 3 years</p><button type="button" onClick={() => setToast("Grace's Farm profile is open.")} className="mt-2 min-h-12 text-sm font-black text-[#4A7C59] underline underline-offset-2">View farm →</button></div><div className="mt-4 border-t border-[#EEE8DB] pt-4"><h2 className="font-black">From farm to your table</h2><p className="mt-2 text-sm leading-6 text-[#605D53]">Flock B · Vaccinated Nov 12 · Collected Nov 21 at 6:40 AM</p><p className="mt-2 text-sm text-[#605D53]">Free-range hens, gathered by hand each morning and carefully packed for your family.</p></div><div className="mt-4 flex items-center gap-3"><span className="text-sm font-extrabold">How many trays?</span><QuantityStepper value={detailQuantity} onChange={setDetailQuantity} /></div></div></div></Card>
            <div className="fixed inset-x-0 bottom-16 z-20 border-t border-[#E8DEC9] bg-[#FFFCF4]/95 p-3 backdrop-blur md:bottom-0"><div className="mx-auto flex max-w-6xl items-center justify-between gap-3"><div><p className="text-xs font-bold text-[#777263]">TOTAL · {detailQuantity} trays</p><p className="text-lg font-black">{money(product.price * detailQuantity)}</p></div><Button kind="accent" className="min-w-44" onClick={() => addToCart(product, detailQuantity)}>Add to cart — {money(product.price * detailQuantity)}</Button></div></div>
        </div>;

        if (screen === 'tracking') return <div className="mx-auto max-w-3xl space-y-5"><div><p className="text-sm font-extrabold text-[#4A7C59]">ORDER ORD-1041</p><h1 className="mt-1 text-2xl font-black">Your fresh order is on its way</h1><p className="mt-1 text-sm font-bold text-[#777263]">20 broilers · Grace's Farm · Placed Nov 21, 8:02 AM</p></div><Card className="p-5 sm:p-7"><div className="flex flex-wrap items-center justify-between gap-3"><div><p className="text-sm font-bold text-[#777263]">DELIVERY TO</p><p className="mt-1 font-black">Kenyatta Avenue, Nakuru</p></div><StatusChip status="Out for delivery" /></div><div className="mt-6"><Timeline items={[{ title: 'Order placed', time: 'Nov 21 · 8:02 AM', done: true }, { title: 'Packed with care', time: '8:45 AM', done: true }, { title: 'Out for delivery', time: 'Rider James · Call rider', current: true, detail: 'Your order is on the way to you.' }, { title: 'Delivered to your door', time: 'ETA 10:30 AM', done: false }]} /></div></Card><div className="grid gap-3 sm:grid-cols-2"><Card className="p-4"><p className="text-sm font-bold text-[#777263]">RIDER</p><p className="mt-1 text-lg font-black">🚴 James Wanjala</p><button type="button" onClick={() => setToast('Calling rider James Wanjala…')} className="mt-2 min-h-12 font-black text-[#4A7C59]">📞 Call James</button></Card><Card className="p-4"><p className="text-sm font-bold text-[#777263]">LIVE DELIVERY</p><p className="mt-1 text-lg font-black">🚚 2.1 km away · ~18 min</p><p className="mt-2 text-sm font-bold text-[#3E8E5A]">🌡️ Freshness check: 4°C</p></Card></div><AlertBanner tone="success">Your order is kept cool and protected. Confirm delivery when everything arrives fresh.</AlertBanner></div>;

        if (screen === 'chat') return <div className="mx-auto max-w-2xl"><Card className="overflow-hidden"><div className="border-b border-[#E8DEC9] bg-[#F7F3E8] p-4 sm:p-5"><p className="text-xs font-black uppercase tracking-wide text-[#4A7C59]">Chat with FeedMaster Supplies</p><h1 className="mt-1 text-lg font-black">Re: Layer Mash 50kg — asking {money(1800)}/bag</h1></div><div className="space-y-4 p-4 sm:p-6"><ChatBubble who="FeedMaster" time="9:12 AM">Best I can do is KES 1,750 per bag for 20 or more bags.</ChatBubble><ChatBubble who="Grace" time="9:14 AM">Can you include free delivery to Nakuru?</ChatBubble><ChatBubble who="FeedMaster" time="9:15 AM">Yes, free delivery on orders above 15 bags. ✅</ChatBubble>{sentMessages.map((item, index) => <ChatBubble key={index} who="Grace" time="Just now">{item}</ChatBubble>)}</div><div className="flex flex-wrap gap-2 border-t border-[#E8DEC9] px-4 py-3"><Button kind="soft" onClick={() => setToast('Offer accepted: KES 1,750 per bag for 20 bags.')}>Accept offer</Button><Button kind="outline" onClick={() => setMessage('Could you do KES 1,700 per bag for 20 bags?')}>Counter offer</Button><Button kind="outline" onClick={() => setMessage('What day can you deliver to Nakuru?')}>Ask about delivery</Button></div><form className="flex gap-2 border-t border-[#E8DEC9] p-3" onSubmit={(event) => { event.preventDefault(); if (message.trim()) { setSentMessages((items) => [...items, message.trim()]); setMessage(''); } }}><label htmlFor="chat-message" className="sr-only">Write a message</label><input id="chat-message" value={message} onChange={(event) => setMessage(event.target.value)} placeholder="Write a message…" className="min-h-12 min-w-0 flex-1 rounded-xl border-[#DED5C1] text-sm" /><Button type="submit">Send</Button></form></Card><p className="mt-3 text-center text-xs font-bold text-[#777263]">Keep payments and agreements in FarmFresh to stay protected.</p></div>;

        if (screen === 'checkout') return <div className="mx-auto max-w-3xl space-y-5"><div><p className="text-sm font-extrabold text-[#4A7C59]">SAFE AND SIMPLE CHECKOUT</p><h1 className="mt-1 text-2xl font-black">Your basket</h1><p className="mt-1 text-sm font-bold text-[#777263]">Your fresh picks, delivered with care.</p></div><div className="grid gap-5 lg:grid-cols-[1fr_0.85fr]"><Card className="p-4 sm:p-5"><h2 className="text-lg font-black">Order summary</h2>{cartItems.length ? cartItems.map((item) => <div key={item.id} className="flex items-center gap-3 border-b border-[#EEE8DB] py-4"><img src={item.image} alt="" className="h-16 w-16 rounded-xl object-cover" /><div className="min-w-0 flex-1"><p className="font-black">{item.quantity}× {item.name}</p><p className="text-sm font-bold text-[#777263]">{item.detail}</p></div><div className="text-right"><p className="font-black">{money(item.price * item.quantity)}</p><div className="mt-1 flex items-center justify-end gap-2"><button type="button" aria-label={`Remove one ${item.name}`} onClick={() => setCart((current) => ({ ...current, [item.id]: Math.max(0, current[item.id] - 1) }))} className="min-h-10 min-w-10 rounded-lg text-lg font-black text-[#4A7C59]">−</button><span className="text-xs font-bold">{item.quantity}</span><button type="button" aria-label={`Add one ${item.name}`} onClick={() => addToCart(item)} className="min-h-10 min-w-10 rounded-lg text-lg font-black text-[#4A7C59]">+</button></div></div></div>) : <EmptyState title="Your basket is empty 🧺" body="Pick something fresh from a nearby farm." action={<Button kind="soft" className="mt-4" onClick={() => go('market')}>Browse fresh eggs</Button>} />}<div className="space-y-2 pt-4 text-sm"><div className="flex justify-between"><span className="font-bold text-[#777263]">Delivery</span><strong>{money(delivery)}</strong></div><div className="flex justify-between border-t border-[#EEE8DB] pt-3 text-base"><strong>Total</strong><strong className="text-lg">{money(cartSubtotal + delivery)}</strong></div></div></Card><div className="space-y-4"><Card className="p-4 sm:p-5"><h2 className="text-lg font-black">Choose how to pay</h2><div className="mt-3 space-y-2">{[['M-Pesa', '📱', 'Paybill 400200'], ['Card', '💳', 'Visa or Mastercard'], ['Cash on delivery', '💵', 'Add KES 20 service fee']].map(([name, icon, desc]) => <button type="button" key={name} onClick={() => setPaymentMethod(name)} aria-pressed={paymentMethod === name} className={`flex min-h-14 w-full items-center gap-3 rounded-xl border p-3 text-left ${paymentMethod === name ? 'border-[#4A7C59] bg-[#F0F6ED]' : 'border-[#E8DEC9] bg-white'}`}><span className="text-xl" aria-hidden="true">{icon}</span><span className="flex-1"><span className="block text-sm font-black">{name}</span><span className="block text-xs font-bold text-[#777263]">{desc}</span></span><span className={`h-5 w-5 rounded-full border-2 ${paymentMethod === name ? 'border-[#4A7C59] bg-[#4A7C59] shadow-[inset_0_0_0_3px_white]' : 'border-[#C9C1B1]'}`} aria-hidden="true" /></button>)}</div></Card><AlertBanner tone="success">🔒 Your money is held safely until you confirm your order arrived fresh.</AlertBanner><Button className="w-full" kind="accent" disabled={cartCount === 0} onClick={() => setToast(`Payment request sent by ${paymentMethod}. Follow the prompt on your phone.`)}>Pay {money(cartSubtotal + delivery + (paymentMethod === 'Cash on delivery' && cartCount ? 20 : 0))} with {paymentMethod}</Button><p className="text-center text-xs font-bold text-[#777263]">Delivery to Kenyatta Avenue, Nakuru · Change address</p></div></div></div>;

        if (screen === 'states') return <div className="space-y-5"><div><p className="text-sm font-extrabold text-[#4A7C59]">HELP WHEN YOU NEED IT</p><h1 className="mt-1 text-2xl font-black">Helpful updates</h1><p className="mt-1 text-sm font-bold text-[#777263]">Clear next steps, even when your connection is slow.</p></div><div className="grid gap-4 lg:grid-cols-2"><Card className="p-4"><h2 className="mb-3 text-base font-black">Empty basket</h2><EmptyState title="Your basket is empty 🧺" body="Pick something fresh from a nearby farm." action={<Button kind="soft" className="mt-4" onClick={() => go('market')}>Browse fresh eggs</Button>} /></Card><Card className="p-4"><h2 className="mb-3 text-base font-black">Loading products</h2><div className="grid grid-cols-2 gap-3"><SkeletonCard /><SkeletonCard /></div><p className="mt-3 text-xs font-bold text-[#777263]">FarmFresh is finding today's fresh picks…</p></Card><Card className="p-4"><h2 className="mb-3 text-base font-black">Could not load orders</h2><AlertBanner tone="error" action={<Button kind="outline" onClick={() => setToast('Orders refreshed at 8:42 AM.')}>Retry</Button>}>We couldn't load orders just now. Your order history is safe; please try again.</AlertBanner></Card><Card className="space-y-3 p-4"><h2 className="text-base font-black">Connection and stock</h2><AlertBanner tone="info">📴 Offline — showing data synced 2 hrs ago. You can still browse saved products.</AlertBanner><AlertBanner tone="error">Only 3 trays left! Order soon from Grace's Farm.</AlertBanner></Card></div></div>;

        if (screen === 'notifications') return <div className="mx-auto max-w-2xl space-y-5"><div className="flex items-end justify-between gap-3"><div><p className="text-sm font-extrabold text-[#4A7C59]">YOUR FARMFRESH UPDATES</p><h1 className="mt-1 text-2xl font-black">Notifications</h1></div><button type="button" onClick={() => setToast('All notifications marked as read.')} className="min-h-12 px-2 text-sm font-black text-[#4A7C59]">Mark all read</button></div><NotificationsCard /></div>;
        if (screen === 'profile') return <div className="mx-auto max-w-2xl space-y-5"><div><p className="text-sm font-extrabold text-[#4A7C59]">YOUR FARMFRESH ACCOUNT</p><h1 className="mt-1 text-2xl font-black">Your profile</h1><p className="mt-1 text-sm font-bold text-[#777263]">Keep your details up to date for smooth deliveries.</p></div><Card className="p-5 sm:p-6"><div className="flex items-center gap-4"><span className="flex h-16 w-16 items-center justify-center rounded-full bg-[#EAF2E6] text-2xl font-black text-[#315D3E]">GW</span><div><h2 className="text-xl font-black">{role === 'Supplier' ? 'Naomi Wambui' : 'Grace Wanjiku'}</h2><p className="mt-1 text-sm font-bold text-[#777263]">{role === 'Supplier' ? 'FeedMaster Supplies · Verified supplier' : role === 'Farmer' ? "Grace's Farm · Verified farmer" : 'FarmFresh buyer · Verified account'}</p><p className="mt-1 text-xs font-bold text-[#777263]">Member since March 2023 · ⭐ 4.9 community rating</p></div></div><div className="mt-5 grid gap-3 border-t border-[#EEE8DB] pt-4 sm:grid-cols-2"><div><p className="text-xs font-extrabold uppercase tracking-wide text-[#777263]">Phone</p><p className="mt-1 font-black">0712 345 678</p></div><div><p className="text-xs font-extrabold uppercase tracking-wide text-[#777263]">Delivery area</p><p className="mt-1 font-black">Kenyatta Avenue, Nakuru</p></div><div><p className="text-xs font-extrabold uppercase tracking-wide text-[#777263]">Languages</p><p className="mt-1 font-black">English · Kiswahili</p></div><div><p className="text-xs font-extrabold uppercase tracking-wide text-[#777263]">Preferred contact</p><p className="mt-1 font-black">M-Pesa message · SMS</p></div></div><Button kind="outline" className="mt-5 w-full" onClick={() => setToast('Profile editing is ready to connect to your account.')}>✎ Edit profile details</Button></Card><Card className="divide-y divide-[#EEE8DB] p-2"><button type="button" className="flex min-h-12 w-full items-center justify-between rounded-xl px-3 text-left text-sm font-extrabold hover:bg-[#F8F4EA]" onClick={() => setToast('Delivery address: Kenyatta Avenue, Nakuru.')}>📍 Saved delivery address <span>→</span></button><button type="button" className="flex min-h-12 w-full items-center justify-between rounded-xl px-3 text-left text-sm font-extrabold hover:bg-[#F8F4EA]" onClick={() => go('chat')}>💬 Get help in your language <span>→</span></button><button type="button" className="flex min-h-12 w-full items-center justify-between rounded-xl px-3 text-left text-sm font-extrabold hover:bg-[#F8F4EA]" onClick={() => setToast('FarmFresh demo account signed out.')}>↪ Sign out <span>→</span></button></Card></div>;
        return null;
    })();

    return <div className={`min-h-screen ${highContrast ? 'contrast-125' : ''}`}>
        {offline && <div className="sticky top-0 z-40 bg-[#2B2B2B] px-4 py-2 text-center text-sm font-extrabold text-white">📴 Offline — showing saved farm data. Last synced today at {lastSynced}. <button type="button" className="ml-2 min-h-10 underline underline-offset-2" onClick={() => setOffline(false)}>Dismiss</button></div>}
        <header className="sticky top-0 z-30 border-b border-[#E8DEC9] bg-[#FFFCF4]/95 backdrop-blur">
            <div className="mx-auto flex h-[68px] max-w-[1440px] items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                <button type="button" className="flex min-h-12 items-center gap-2 text-left" onClick={() => go('welcome')} aria-label="FarmFresh home"><span className="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#EAF2E6] text-2xl" aria-hidden="true">🐔</span><span><span className="block text-lg font-black leading-5 text-[#315D3E]">FarmFresh</span><span className="hidden text-[10px] font-extrabold text-[#777263] sm:block">Good food, closer to home</span></span></button>
                <div className="flex items-center gap-2">
                    <span className="hidden text-xs font-bold text-[#777263] sm:inline">Last synced 8:42 AM</span>
                    <button type="button" onClick={() => setOffline((value) => !value)} className="min-h-12 rounded-xl border border-[#DED5C1] bg-white px-3 text-sm font-extrabold" aria-pressed={offline}>{offline ? '📴 Offline' : '🟢 Online'}</button>
                    <button type="button" onClick={() => setHighContrast((value) => !value)} className="min-h-12 rounded-xl border border-[#DED5C1] bg-white px-3 text-sm font-extrabold" aria-pressed={highContrast} aria-label="Toggle high contrast">◐ <span className="hidden sm:inline">Contrast</span></button>
                    <button type="button" onClick={() => go('notifications')} className="relative flex h-12 min-w-12 items-center justify-center rounded-xl border border-[#DED5C1] bg-white text-lg" aria-label="Notifications, 3 unread">🔔<span className="absolute right-1 top-1 h-2.5 w-2.5 rounded-full bg-[#C96F4A]" /></button>
                </div>
            </div>
        </header>
        <div className="mx-auto flex max-w-[1440px]">
            <aside className="sticky top-[68px] hidden h-[calc(100vh-68px)] w-64 shrink-0 flex-col border-r border-[#E8DEC9] bg-[#FFFCF4] p-4 lg:flex">
                <div className="mb-4 rounded-[14px] bg-[#F2EEE3] p-3"><p className="text-[11px] font-extrabold uppercase tracking-wide text-[#777263]">You are using</p><label htmlFor="role-picker" className="sr-only">Choose your role</label><select id="role-picker" value={role} onChange={(event) => switchRole(event.target.value)} className="mt-1 min-h-12 w-full rounded-xl border-[#DED5C1] bg-white text-sm font-black focus:border-[#4A7C59] focus:ring-[#4A7C59]"><option>Farmer</option><option>Buyer</option><option>Supplier</option></select></div>
                <nav aria-label="FarmFresh screens" className="space-y-1">{screens.map(([id, icon, label]) => navButton(id, icon, label))}</nav>
                <div className="mt-auto rounded-[14px] border border-[#DFE8D8] bg-[#F0F6ED] p-3"><p className="text-sm font-black text-[#315D3E]">Need a hand?</p><p className="mt-1 text-xs font-bold leading-5 text-[#5D6758]">Ask us in your language. We’re here for your farm.</p><button type="button" className="mt-2 min-h-12 text-sm font-black text-[#315D3E]" onClick={() => go('chat')}>💬 Talk to our team</button></div>
            </aside>
            <main id="main-content" className="min-w-0 flex-1 px-4 pb-28 pt-5 sm:px-6 sm:pt-7 lg:px-8 lg:pb-8" aria-live="polite">
                <div className="mb-5 flex flex-wrap items-center justify-between gap-3 lg:hidden">
                    <label className="flex items-center gap-2 text-xs font-extrabold text-[#656254]" htmlFor="mobile-role-picker">I am a
                        <select id="mobile-role-picker" value={role} onChange={(event) => switchRole(event.target.value)} className="min-h-12 rounded-xl border-[#DED5C1] bg-white text-sm font-black focus:border-[#4A7C59] focus:ring-[#4A7C59]"><option>Farmer</option><option>Buyer</option><option>Supplier</option></select>
                    </label>
                    {screen !== 'welcome' && <div className="flex gap-2 overflow-x-auto">{screens.filter((item) => item[0] !== 'welcome').map(([id, icon, label]) => <button key={id} type="button" className={`min-h-12 shrink-0 rounded-xl px-3 text-xs font-extrabold ${screen === id ? 'bg-[#4A7C59] text-white' : 'border border-[#DED5C1] bg-white text-[#5F5B50]'}`} onClick={() => go(id)}>{icon} {label}</button>)}</div>}
                </div>
                {content}
            </main>
        </div>
        <BottomNav activeScreen={screen} go={go} cartCount={cartCount} role={role} />
        <Toast message={toast} onClose={() => setToast('')} />
    </div>;
}

function NotificationsCard() {
    const notifications = [
        ['🥚', 'New order #ORD-1042', 'John M. · KES 450', '2m ago', true],
        ['💰', 'Payment received', 'Mama Njeri Cafe · KES 9,000', '1h ago', true],
        ['⚠️', 'Feed stock is low', 'Reorder Layer Mash from FeedMaster', '3h ago', false],
        ['⭐', 'Peter K. left a 5-star review', '“Fresh chicks and kind service.”', 'Yesterday', false],
    ];
    return <Card className="divide-y divide-[#EEE8DB] p-4 sm:p-5">{notifications.map(([icon, title, detail, time, unread]) => <div key={title} className="flex items-start gap-3 py-3 first:pt-0 last:pb-0"><span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#F5F0E4] text-xl" aria-hidden="true">{icon}</span><div className="min-w-0 flex-1"><div className="flex flex-wrap items-center gap-2"><h2 className="text-sm font-black">{title}</h2>{unread && <span className="h-2 w-2 rounded-full bg-[#C96F4A]" aria-label="Unread" />}</div><p className="mt-1 text-sm font-bold text-[#696557]">{detail}</p></div><time className="shrink-0 text-xs font-bold text-[#858071]">{time}</time></div>)}</Card>;
}

const rootElement = document.getElementById('farmfresh-app');
if (rootElement) {
    createRoot(rootElement).render(<FarmFreshApp />);
}

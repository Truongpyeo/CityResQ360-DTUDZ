export const metadata = {
  title: "Flood Zone Details",
};

export default function Page({ params }: { params: { id: string } }) {
  return (
    <main>
      <h1>Flood Zone Details</h1>
      <p>Details for a specific flood zone. Currently viewing record {params.id}.</p>
    </main>
  );
}

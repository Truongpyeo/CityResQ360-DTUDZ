export const metadata = {
  title: "Incident Alerts",
};

type PageParams = Promise<{ id: string }>;

export default async function Page({ params }: { params: PageParams }) {
  const { id } = await params;

  return (
    <main>
      <h1>Incident Alerts</h1>
      <p>Alerts associated with this incident. Currently viewing record {id}.</p>
    </main>
  );
}
